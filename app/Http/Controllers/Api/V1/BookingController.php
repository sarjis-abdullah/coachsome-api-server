<?php

namespace App\Http\Controllers\Api\V1;

use App\Data\BookingStatus;
use App\Data\Constants;
use App\Data\OrderStatus;
use App\Data\StatusCode;
use App\Entities\Booking;
use App\Entities\Message;
use App\Entities\Package;
use App\Entities\User;
use App\Http\Controllers\Controller;
use App\Http\Resources\Package\PackageResource;
use App\Http\Resources\Package\PackageSetting;
use App\Http\Resources\Profile\ProfileCardResource;
use App\Mail\AthleteDeclinedPackage;
use App\Mail\AthletePackageConfirmation;
use App\Mail\CoachPackageConfirmation;
use App\Mail\PackageAccepted;
use App\Services\BookingService;
use App\Services\ContactService;
use App\Services\CurrencyService;
use App\Services\Media\MediaService;
use App\Services\MessageFormatterService;
use App\Services\OrderService;
use App\Services\PackageService;
use App\Services\QuickpayClientService;
use App\ValueObjects\Message\BigText;
use App\ValueObjects\Message\AcceptedPackageBooking;
use App\ValueObjects\Message\DeclinedPackageBooking;
use Carbon\Carbon;
use Illuminate\Http\Request;
use AshAllenDesign\LaravelExchangeRates\Classes\ExchangeRate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use QuickPay\QuickPay;
use Exception;

class BookingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        try {
            $request->validate([
                'packageId' => 'required'
            ]);

            $packageId = $request->packageId;
            $requestedCurrencyCode = $request->header('Currency-Code');

            if (!$requestedCurrencyCode) {
                throw new \Exception('Currency not found');
            }

            $package = Package::find($packageId);

            if (!$package) {
                throw new Exception('Package not found');
            }

            $packageOwnerUser = $package->user;
            $packageCategory = $package->category;
            $userPackageSetting = $packageOwnerUser->ownPackageSetting;
            $authUser = Auth::user();

            if (!$packageOwnerUser) {
                throw new \Exception('Package owner user not found');
            }

            $packageService = new PackageService();
            $currencyService = new CurrencyService();
            $mediaService = new MediaService();

            $fromCurrencyCode = $currencyService->getUserCurrency($packageOwnerUser)->code;
            $toCurrencyCode = $requestedCurrencyCode ?? $currencyService->getDefaultBasedCurrency()->code;

            $discount = $package->details->discount ?? 0.00;
            $originalPrice = $packageService->calculateOriginalPrice($packageOwnerUser, $package);
            $salePrice = $packageService->calculatePackageSalePrice($originalPrice, $discount);
            if ($packageCategory && $packageCategory->id == Constants::PACKAGE_CAMP_ID) {
                $minPerson = $package->details->attendees_min;
                $maxPerson = $package->details->attendees_max;
            } else {
                $minPerson = 1;
                $maxPerson = 1;
            }

            $salePriceAfterConvertingCurrency = $currencyService->convert(
                $salePrice,
                $fromCurrencyCode,
                $toCurrencyCode
            );

            $serviceFee = round((5 / 100) * $salePriceAfterConvertingCurrency, 2);
            $total = round(($salePriceAfterConvertingCurrency + $serviceFee), 2);
            $totalPerPerson = round((1 * ($salePriceAfterConvertingCurrency + $serviceFee)), 2);

            $chargeBox = new \stdClass();
            $chargeBox->priceForPackage = $salePriceAfterConvertingCurrency;
            $chargeBox->totalPerPerson = $totalPerPerson;
            $chargeBox->total = $total;
            $chargeBox->salePrice = round($salePriceAfterConvertingCurrency, 2);
            $chargeBox->serviceFee = $serviceFee;
            $chargeBox->minPerson = $minPerson;
            $chargeBox->maxPerson = $maxPerson;

            $availabilities = $packageOwnerUser->availabilities;

            $packageInfo = new PackageResource($package);
            $packageSetting = new PackageSetting($userPackageSetting);
            $profileCard = new ProfileCardResource($packageOwnerUser->profile, $mediaService);

            return response()->json([
                'status' => 'success',
                'packageInfo' => $packageInfo,
                'packageSetting' => $packageSetting,
                'profileCard' => $profileCard,
                'chargeBox' => $chargeBox,
                'availabilities' => $availabilities
            ], StatusCode::HTTP_OK);

        } catch (\Exception $e) {
            if ($e instanceof ValidationException) {
                return response()->json(
                    $e->validator->errors()->first(),
                    StatusCode::HTTP_UNPROCESSABLE_ENTITY
                );
            }

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], StatusCode::HTTP_UNPROCESSABLE_ENTITY);
        }

    }

    public function getBookingPackage(Request $request)
    {
        try {
            $selectedUserId = $request->query('selectedUserId');

            $authUser = Auth::user();
            $selectedUser = User::find($selectedUserId);


            if (!$authUser) {
                throw new \Exception('User not found');
            }

            if (!$selectedUser) {
                throw new \Exception('User not found');
            }


            $purchasedPackageBookings = Booking::with(['order', 'bookingTimes'])->where('package_buyer_user_id', $authUser->id)
                ->where('status', 'Accepted')
                ->where('package_owner_user_id', $selectedUser->id)
                ->get()
                ->filter(function ($item) {
                    $order = $item->order;
                    $packageSnapshot = $order ? json_decode($order->package_snapshot) : null;
                    $packageDetails = $packageSnapshot ? $packageSnapshot->details : null;
                    $session = $packageDetails ? $packageDetails->session : 0;
                    if ($session) {
                        $acceptedBookingTimeCount = $item->bookingTimes->where('status', 'Accepted')->count();
                        if ($session > $acceptedBookingTimeCount) {
                            return true;
                        } else {
                            return false;
                        }
                    } else {
                        return false;
                    }
                })
                ->values()
                ->map(function ($item) {
                    $remainingSession = 0;
                    $info = [
                        'orderKey' => '',
                        'bookingDate' => '',
                        'status' => '',
                        'isQuickBooking' => '',
                    ];

                    $order = $item->order;
                    $packageSnapshot = $order ? json_decode($order->package_snapshot) : null;
                    $packageDetails = $packageSnapshot ? $packageSnapshot->details : null;
                    $session = $packageDetails ? $packageDetails->session : 0;

                    if ($order) {
                        $info['orderKey'] = $order->key;
                        $info['bookingDate'] = date('d-m-Y', strtotime($item->booking_date));
                        $info['status'] = $item->status;
                        $info['isQuickBooking'] = $item->is_quick_booking;
                    }

                    if ($session) {
                        $acceptedBookingTimeCount = $item->bookingTimes->where('status', 'Accepted')->count();
                        $remainingSession = $session - $acceptedBookingTimeCount;
                    }

                    return [
                        'packageOwnerUserId' => $item->packageOwnerUser->id,
                        'packageBuyerUserId' => $item->packageBuyerUser->id,
                        'bookingId' => $item->id,
                        'orderId' => $item->order->id,
                        'packageInfo' => json_decode($item->order->package_snapshot),
                        'isSold' => false,
                        'totalSession' => $session,
                        'remainingSession' => $remainingSession,
                        'info' => $info
                    ];;
                });

            $soldPackageBookings = Booking::with(['order', 'bookingTimes'])->where('package_buyer_user_id', $selectedUser->id)
                ->where('status', 'Accepted')
                ->where('package_owner_user_id', $authUser->id)
                ->get()
                ->filter(function ($item) {
                    $order = $item->order;
                    $packageSnapshot = $order ? json_decode($order->package_snapshot) : null;
                    $packageDetails = $packageSnapshot ? $packageSnapshot->details : null;
                    $session = $packageDetails ? $packageDetails->session : 0;
                    if ($session) {
                        $acceptedBookingTimeCount = $item->bookingTimes->where('status', 'Accepted')->count();
                        if ($session > $acceptedBookingTimeCount) {
                            return true;
                        } else {
                            return false;
                        }
                    } else {
                        return false;
                    }
                })
                ->values()
                ->map(function ($item) {
                    $remainingSession = 0;
                    $info = [
                        'orderKey' => '',
                        'bookingDate' => '',
                        'status' => '',
                        'isQuickBooking' => '',
                    ];

                    $order = $item->order;
                    $packageSnapshot = $order ? json_decode($order->package_snapshot) : null;
                    $packageDetails = $packageSnapshot ? $packageSnapshot->details : null;
                    $session = $packageDetails ? $packageDetails->session : 0;

                    if ($order) {
                        $info['orderKey'] = $order->key;
                        $info['bookingDate'] = date('d-m-Y', strtotime($item->booking_date));
                        $info['status'] = $item->status;
                        $info['isQuickBooking'] = $item->is_quick_booking;
                    }

                    if ($session) {
                        $acceptedBookingTimeCount = $item->bookingTimes->where('status', 'Accepted')->count();
                        $remainingSession = $session - $acceptedBookingTimeCount;
                    }

                    return [
                        'packageOwnerUserId' => $item->packageOwnerUser->id,
                        'packageBuyerUserId' => $item->packageBuyerUser->id,
                        'bookingId' => $item->id,
                        'orderId' => $item->order->id,
                        'packageInfo' => json_decode($item->order->package_snapshot),
                        'isSold' => true,
                        'totalSession' => $session,
                        'remainingSession' => $remainingSession,
                        'info' => $info
                    ];
                });

            return response()->json([
                'userName' => $selectedUser->user_name,
                'purchasedPackages' => $purchasedPackageBookings,
                'soldPackages' => $soldPackageBookings,
            ],
                StatusCode::HTTP_OK
            );


        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ],
                StatusCode::HTTP_UNPROCESSABLE_ENTITY
            );
        }
    }

    public function changeStatus(Request $request)
    {
        try {

            $bookingId = $request->bookingId;
            $action = $request->action;

            $booking = Booking::find($bookingId);
            if (!$booking) {
                throw new \Exception('Booking information not found', 101);
            }

            $authUser = Auth::user();
            $packageOwnerUser = User::find($booking->package_owner_user_id);
            $packageBuyerUser = User::find($booking->package_buyer_user_id);


            if (!$packageOwnerUser || !$packageBuyerUser) {
                throw new \Exception('User not found', 102);
            }

            if ($booking->status == 'Accepted') {
                throw new \Exception('Package has already accepted.', 103);
            }

            if ($booking->status == 'Declined') {
                throw new \Exception('Package has already declined.', 104);
            }

            if ($authUser->id != $packageOwnerUser->id) {
                throw new \Exception('You do not have permission for this action.', 105);
            }

            $paymentId = null;
            $newMessage = null;
            $responseMessage = 'This request is still pending';

            $order = $booking->order;
            $payment = $order->payment ?? null;
            if ($payment) {
                $paymentId = json_decode($payment->details)->payment_id;
            }

            $contactService = new ContactService();
            $quickpayClientService = new QuickpayClientService();
            $messageFormatterService = new MessageFormatterService();


            $quickpayClient = $quickpayClientService->getClient();

            // Accept
            if ($action == 'accept') {

                $captureRequest = $quickpayClient->request->post(sprintf("/payments/%s/capture", $paymentId), [
                    'amount' => $order->total_amount * 100
                ]);
                if ($captureRequest->httpStatus() == 202) {
                    $order->status = OrderStatus::CAPTURE;
                    $order->save();
                    $booking->status = BookingStatus::ACCEPTED;
                    $booking->date_of_acceptance = date('Y-m-d H:i:s');
                    $booking->save();
                    Mail::to($packageOwnerUser)->send(new CoachPackageConfirmation($booking));
                    Mail::to($packageBuyerUser)->send(new AthletePackageConfirmation($booking));
                } else {
                    throw new \Exception('Payment is not captured properly, try again', 106);
                }

                $acceptedPackageBookingMessage = new AcceptedPackageBooking([
                    'orderSnapshot' => $order->toArray(),
                    'packageSnapshot' => json_decode($order->package_snapshot),
                    'status' => 'Accepted',
                ]);
                $newMessage = new Message();
                $newMessage->sender_user_id = $authUser->id;
                $newMessage->receiver_user_id = $packageBuyerUser->id;
                $newMessage->type = 'structure';
                $newMessage->structure_content = $acceptedPackageBookingMessage->toJson();
                $newMessage->date_time = Carbon::now();
                $newMessage->save();

                $responseMessage = 'This request was successfully accepted.';
                $contactService->updateLastMessageAndTime($authUser, $packageBuyerUser, $newMessage);

            }

            // Decline
            if ($action == 'decline') {

                $cancelRequest = $quickpayClient->request->post(sprintf("/payments/%s/cancel", $paymentId));
                Log::info(print_r($cancelRequest->asObject(), true));
                if ($cancelRequest->httpStatus() == 202) {
                    $order->status = OrderStatus::CANCELED;
                    $order->save();
                    $booking->status = BookingStatus::DECLINED;
                    $booking->date_of_decline = date('Y-m-d H:i:s');
                    $booking->save();
                    Mail::to($packageBuyerUser)->send(new AthleteDeclinedPackage($booking));
                } else {
                    throw new \Exception('Payment is not canceled properly, try again', 106);
                }

                $declinedPackageBookingMessage = new DeclinedPackageBooking([
                    'orderSnapshot' => $order->toArray(),
                    'packageSnapshot' => json_decode($order->package_snapshot),
                    'status' => 'Accepted',
                ]);
                $newMessage = new Message();
                $newMessage->sender_user_id = $authUser->id;
                $newMessage->receiver_user_id = $packageBuyerUser->id;
                $newMessage->type = 'structure';
                $newMessage->structure_content = $declinedPackageBookingMessage->toJson();
                $newMessage->date_time = Carbon::now();
                $newMessage->save();

                $responseMessage = 'This request was declined.';
                $contactService->updateLastMessageAndTime($authUser, $packageBuyerUser, $newMessage);

            }

            // All message
            $messages = Message::where(function ($q) use ($packageOwnerUser, $packageBuyerUser) {
                $q->where('sender_user_id', $packageOwnerUser->id);
                $q->where('receiver_user_id', $packageBuyerUser->id);
            })->orWhere(function ($q) use ($packageOwnerUser, $packageBuyerUser) {
                $q->where('sender_user_id', $packageBuyerUser->id);
                $q->where('receiver_user_id', $packageOwnerUser->id);
            })->get()->map(function ($item) use ($messageFormatterService) {
                return $messageFormatterService->doFormat($item);
            });

            return response()->json([
                'message' => $responseMessage,
                'messages' => $messages,
                'newMessage' => $messageFormatterService->doFormat($newMessage)
            ],
                StatusCode::HTTP_OK
            );

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'erroCode' => $e->getCode()
            ],
                StatusCode::HTTP_UNPROCESSABLE_ENTITY
            );
        }

    }
}
