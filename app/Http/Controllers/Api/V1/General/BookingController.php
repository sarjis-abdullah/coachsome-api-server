<?php

namespace App\Http\Controllers\Api\V1\General;

use App\Data\BookingStatus;
use App\Data\Constants;
use App\Data\MessageData;
use App\Data\OrderStatus;
use App\Data\Promo;
use App\Data\StatusCode;
use App\Data\TransactionType;
use App\Entities\Booking;
use App\Entities\Currency;
use App\Entities\GiftTransaction;
use App\Entities\Message;
use App\Entities\Package;
use App\Entities\PromoCode;
use App\Entities\User;
use App\Http\Controllers\Controller;
use App\Http\Resources\Package\PackageResource;
use App\Http\Resources\Package\PackageSetting;
use App\Http\Resources\Profile\ProfileCardResource;
use App\Mail\AthleteDeclinedPackage;
use App\Mail\AthletePackageConfirmation;
use App\Mail\CoachPackageConfirmation;
use App\Mail\NewOrderCapture;
use App\Services\ContactService;
use App\Services\CurrencyService;
use App\Services\GiftCard\GiftCardService;
use App\Services\Media\MediaService;
use App\Services\MessageFormatterService;
use App\Services\PackageService;
use App\Services\Promo\PromoService;
use App\Services\QuickpayClientService;
use App\ValueObjects\Message\AcceptedPackageBooking;
use App\ValueObjects\Message\DeclinedPackageBooking;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
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
                'packageId' => 'required',
                'promoCode' => 'nullable|string',
                'useGiftCard' => 'nullable'
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
            $packageBuyerUser = Auth::user();
            $packageCategory = $package->category;
            $userPackageSetting = $packageOwnerUser->ownPackageSetting;
            if (!$packageOwnerUser) {
                throw new \Exception('Package owner user not found');
            }

            $packageService = new PackageService();
            $promoService = new PromoService();
            $currencyService = new CurrencyService();
            $mediaService = new MediaService();
            $giftCardService = new GiftCardService();

            $toCurrencyCode = $requestedCurrencyCode ?? $currencyService->getDefaultBasedCurrency()->code;

            if ($packageCategory && $packageCategory->id == Constants::PACKAGE_CAMP_ID) {
                $minPerson = $package->details->attendees_min;
                $maxPerson = $package->details->attendees_max;
            } else {
                $minPerson = 1;
                $maxPerson = 1;
            }

            $authUser = Auth::user();

            $giftCardBalance = $giftCardService->balance($authUser);


            // Package charge info
            $chargeInfo = $packageService->chargeInformation($package, $toCurrencyCode, [
                'promoCode' => $request['promoCode'],
                'packageBuyerUser' => $packageBuyerUser,
                'useGiftCard' => $request['useGiftCard']
            ]);

            $chargeBox = new \stdClass();
            $chargeBox->priceForPackage = $chargeInfo['salePrice'];
            $chargeBox->totalPerPerson = $chargeInfo['totalPerPerson'];
            $chargeBox->total = $chargeInfo['total'];
            $chargeBox->salePrice = $chargeInfo['salePrice'];
            $chargeBox->serviceFee = $chargeInfo['serviceFee'];
            $chargeBox->giftPayableAmount = $chargeInfo['giftCard']['payableAmount'];
            $chargeBox->giftBalanceAfterPaid = $chargeInfo['giftCard']['balanceAfterPaid'];
            $chargeBox->minPerson = $minPerson;
            $chargeBox->maxPerson = $maxPerson;

            // Promo Code Info
            $promoCodeInfo = [
                'valid' => false,
                'value' => '',
                'amount' => 0.00,
                'message' => ''
            ];
            $promoCode = PromoCode::where('code', $request['promoCode'])->first();
            if ($promoCode) {
                if (!$promoService->isExpired($promoCode, $packageBuyerUser)) {
                    $promoCodeInfo['valid'] = true;
                    $promoCodeInfo['value'] = $promoCode->code;
                    $promoCodeInfo['amount'] = $chargeInfo['promoDiscount'];
                } else {
                    $promoCodeInfo['message'] = "This code is expired";
                }
            } else {
                $promoCodeInfo['message'] = 'This code is not found';
            }

            // Availabilities
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
                'giftCardBalance' => $giftCardBalance,
                'availabilities' => $availabilities,
                'promoCode' => $promoCodeInfo
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
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
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

            return response()->json(
                [
                    'userName' => $selectedUser->user_name,
                    'purchasedPackages' => $purchasedPackageBookings,
                    'soldPackages' => $soldPackageBookings,
                ],
                StatusCode::HTTP_OK
            );
        } catch (\Exception $e) {
            return response()->json(
                [
                    'message' => $e->getMessage(),
                ],
                StatusCode::HTTP_UNPROCESSABLE_ENTITY
            );
        }
    }

    public function changeStatus(Request $request)
    {
        try {

            // Tracking pixel track the package that accepted
            $trackingPixel = [
                'status' => false,
                'orderKey' => '',
                'salePrice' => 0.00
            ];

            $action = $request->action;
            $bookingId = $request->bookingId;


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
            $isPaymentCapture = false;
            $responseMessage = '';

            $order = $booking->order;
            $payment = $order->payment ?? null;
            if ($payment) {
                $paymentId = json_decode($payment->details)->payment_id;
            }

            $contactService = new ContactService();
            $messageFormatterService = new MessageFormatterService();
            $quickpayClientService = new QuickpayClientService();

            $quickpayClient = $quickpayClientService->getClient();

            // If the package is booked as quick booking then the booking is already captured
            // Check the payment status is it capture or not
            // Update order and booking status according to the payment status result
            if ($payment) {
                $paymentResponse = $quickpayClient->request->get('/payments/' . $paymentId)->asArray();
                if ($paymentResponse) {
                    if ($paymentResponse['operations']) {
                        foreach ($paymentResponse['operations'] as $operation) {
                            if ($operation['type'] == 'capture') {
                                $isPaymentCapture = true;
                            }
                        }
                    }
                }

                if ($isPaymentCapture) {
                    $order->status = OrderStatus::CAPTURE;
                    $order->save();
                    $booking->status = BookingStatus::ACCEPTED;
                    $booking->save();
                    $responseMessage = 'Payment had captured';
                }
            }

            DB::beginTransaction();

            if ($action == 'accept') {

                // Tracking pixel only assign when accept a package request
                $trackingPixel['status'] = true;
                $trackingPixel['orderKey'] = $order->key;
                $trackingPixel['salePrice'] = $order->package_sale_price;

                // If payment is not capture then it needs to capture
                if (!$isPaymentCapture) {
                    if ($paymentId) {
                        $captureRequest = $quickpayClient->request->post(sprintf("/payments/%s/capture", $paymentId), [
                            'amount' => $order->local_total_amount * 100
                        ]);

                        if ($captureRequest->httpStatus() == 202) {
                            $isPaymentCapture = true;

                        } else {
                            throw new \Exception('Payment is not captured properly, try again', 106);
                        }

                    }

                    if ($isPaymentCapture) {
                        $order->status = OrderStatus::CAPTURE;
                        $order->save();
                        $booking->status = BookingStatus::ACCEPTED;
                        $booking->date_of_acceptance = date('Y-m-d H:i:s');
                        $booking->save();

                        // Mail to users
                        Mail::to($packageOwnerUser)->queue(new CoachPackageConfirmation($booking));
                        Mail::to($packageBuyerUser)->queue(new AthletePackageConfirmation($booking));

                        // Mail to administrator
                        Mail::to([config('mail.from.address')])->queue(new NewOrderCapture($order));
                    }

                    $acceptedPackageBookingMessage = new AcceptedPackageBooking([
                        'orderSnapshot' => $order->toArray(),
                        'packageSnapshot' => json_decode($order->package_snapshot),
                        'status' => 'Accepted',
                    ]);

                    $newMessage = new Message();
                    $newMessage->message_category_id = MessageData::CATEGORY_ID_ACCEPTED_PACKAGE_BOOKING;
                    $newMessage->sender_user_id = $authUser->id;
                    $newMessage->receiver_user_id = $packageBuyerUser->id;
                    $newMessage->type = 'structure';
                    $newMessage->structure_content = $acceptedPackageBookingMessage->toJson();
                    $newMessage->date_time = Carbon::now();
                    $newMessage->save();

                    $responseMessage = 'This request was successfully accepted.';
                    $contactService->updateLastMessageAndTime($authUser, $packageBuyerUser, $newMessage);
                }
            }

            if ($action == 'decline') {

                if ($paymentId) {
                    $cancelRequest = $quickpayClient->request->post(sprintf("/payments/%s/cancel", $paymentId));
                    if (!$cancelRequest->httpStatus() == 202) {
                        throw new \Exception('Payment is not canceled properly, try again', 106);
                    }
                }

                $order->status = OrderStatus::CANCELED;
                $order->save();
                $booking->status = BookingStatus::DECLINED;
                $booking->date_of_decline = date('Y-m-d H:i:s');
                $booking->save();

                // Mail to the package buyer so that he can be notified
                Mail::to($packageBuyerUser)->send(new AthleteDeclinedPackage($booking));

                // Redeem the amount that was added to the order
                // Check transaction if it redeems before
                if ($order->gift_transaction_id) {
                    $giftTransaction = GiftTransaction::find($order->gift_transaction_id);
                    if ($giftTransaction) {
                        // Check the transaction redeem before
                        $giftTransactionCredit = GiftTransaction::where('id', $order->gift_transaction_id)
                            ->where('type', TransactionType::DEBIT)
                            ->first();

                        // Redeem the transaction if it is not happened before
                        if (!$giftTransactionCredit) {
                            $newGiftTransaction = new GiftTransaction();
                            $newGiftTransaction->user_id = $giftTransaction->user_id;
                            $newGiftTransaction->gift_order_id = $giftTransaction->gift_order_id;
                            $newGiftTransaction->transaction_date = Carbon::now();
                            $newGiftTransaction->amount = $giftTransaction->amount;
                            $newGiftTransaction->currency = $giftTransaction->currency;
                            $newGiftTransaction->type = TransactionType::DEBIT;
                            $newGiftTransaction->save();

                        }

                    }
                }
                $declinedPackageBookingMessage = new DeclinedPackageBooking([
                    'orderSnapshot' => $order->toArray(),
                    'packageSnapshot' => json_decode($order->package_snapshot),
                    'status' => 'Accepted',
                ]);
                $newMessage = new Message();
                $newMessage->sender_user_id = $authUser->id;
                $newMessage->message_category_id = MessageData::CATEGORY_ID_DECLINED_PACKAGE_BOOKING;
                $newMessage->receiver_user_id = $packageBuyerUser->id;
                $newMessage->type = 'structure';
                $newMessage->structure_content = $declinedPackageBookingMessage->toJson();
                $newMessage->date_time = Carbon::now();
                $newMessage->save();

                $responseMessage = 'This request was declined.';
                $contactService->updateLastMessageAndTime($authUser, $packageBuyerUser, $newMessage);
            }

            DB::commit();

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

            return response([
                'trackingPixel' => $trackingPixel,
                'message' => $responseMessage,
                'messages' => $messages,
                'newMessage' => $messageFormatterService->doFormat($newMessage)
            ], StatusCode::HTTP_OK );
        } catch (\Exception $e) {
            return response(
                [
                    'message' => $e->getMessage(),
                    'erroCode' => $e->getCode()
                ],
                StatusCode::HTTP_UNPROCESSABLE_ENTITY
            );
        }
    }
}
