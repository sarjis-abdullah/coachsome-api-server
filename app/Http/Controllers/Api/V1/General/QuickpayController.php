<?php

namespace App\Http\Controllers\Api\V1\General;

use App\Data\StatusCode;
use App\Entites\PromoUser;
use App\Entities\Booking;
use App\Entities\BookingSetting;
use App\Entities\Contact;
use App\Entities\Message;
use App\Entities\Order;
use App\Entities\Package;
use App\Entities\Payment;
use App\Entities\PromoCode;
use App\Http\Controllers\Controller;
use App\Mail\AthletePackageConfirmation;
use App\Mail\CoachPackageConfirmation;
use App\Mail\CoachPendingPackageRequest;
use App\Mail\NewOrderCapture;
use App\Mail\PackageAccepted;
use App\Mail\AthletePendingPackageRequest;
use App\Services\BookingService;
use App\Services\ContactService;
use App\Services\CurrencyService;
use App\Services\Media\MediaService;
use App\Services\PackageService;
use App\Services\Promo\PromoService;
use App\Services\QuickpayClientService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use QuickPay\QuickPay;

class QuickpayController extends Controller
{
    public function pay(Request $request)
    {

        try {
            $request->validate([
                'packageId' => 'required',
                'currency' => 'required',
                'serviceFee' => 'required',
                'totalAmount' => 'required',
                'salePrice' => 'required',
                'promoCode' => 'nullable'
            ]);

            $packageId = $request->packageId;
            $currencyCode = $request->currency;
            $packageBuyerMessage = $request->message;
            $numberOfAttendees = $request->numberOfAttendees;
            $serviceFee = $request->serviceFee;
            $totalAmount = $request->totalAmount;
            $totalPerPerson = $request->totalPerPerson;
            $salePrice = $request->salePrice;
            $paymentMethod = $request->paymentMethod;
            $continueUrl = $request->packageUrl . "?payment_status=paid";
            $cancelUrl = $request->packageUrl . "?payment_status=cancel";
            $promoCodeValue = $request->promoCode;

            $package = Package::with(['category', 'details', 'user'])
                ->where('id', $packageId)
                ->first();


            if (!$paymentMethod) {
                throw new Exception('Sorry, payment method is not selected');
            }

            if (!$package) {
                throw new Exception('Sorry, package not found');
            }

            if (!$continueUrl || !$cancelUrl) {
                throw new Exception('Something went wrong please, try again');
            }

            $packageCategory = $package->category;
            $packageOwnerUser = $package->user;
            $packageBuyerUser = Auth::user();


            if ($packageOwnerUser->id == $packageBuyerUser->id) {
                throw new \Exception('Sorry, you can not buy your package.');
            }

            if (!$packageOwnerUser) {
                throw new Exception('Sorry, package owner not found');
            }

            if ($packageOwnerUser->id == $packageBuyerUser->id) {
                throw new Exception('You can not buy your package. We can apply it in next version.');
            }

            $contactService = new ContactService();
            $promoService = new PromoService();
            $packageService = new PackageService();
            $currencyService = new CurrencyService();

            $toCurrencyCode = $currencyCode ?? $currencyService->getDefaultBasedCurrency()->code;


            $packageOwnerPackageSetting = $packageOwnerUser->ownPackageSetting;
            $isQuickBooking = $packageOwnerPackageSetting->is_quick_booking ?? false;

            DB::beginTransaction();

            // Booking setting
            $bookingSetting = BookingSetting::get()->first();

            // Create booking
            $booking = new Booking();
            $booking->package_owner_user_id = $packageOwnerUser->id;
            $booking->package_buyer_user_id = $packageBuyerUser->id;
            $booking->package_buyer_message = $packageBuyerMessage;
            $booking->booking_settings_snapshot = $bookingSetting->toJson();
            $booking->package_owner_service_fee_snapshot = $bookingSetting->package_owner_gnr_service_fee;
            $booking->package_buyer_service_fee_snapshot = $bookingSetting->package_buyer_service_fee;
            $booking->hereof_vat_snapshot = $bookingSetting->hereof_vat;
            $booking->booking_date = Carbon::now();
            $booking->is_quick_booking = $isQuickBooking;
            $booking->save();

            // Package charge info
            $chargeInfo = $packageService->chargeInformation($package, $toCurrencyCode, ['promoCode' => $promoCodeValue, 'packageBuyerUser' => $packageBuyerUser]);

            // Creating order
            $order = new Order();
            $order->booking_id = $booking->id;
            $order->package_id = $package->id;
            $order->package_category_id = $packageCategory->id;
            $order->package_snapshot = $package->toJson();
            $order->number_of_attendees = $numberOfAttendees;
            $order->package_sale_price = $chargeInfo['salePrice'];
            $order->total_per_person = $chargeInfo['totalPerPerson'];
            $order->currency = $currencyCode;
            $order->total_amount = $chargeInfo['total'];
            $order->service_fee = $chargeInfo['serviceFee'];
            $order->status = 'Initial';
            $order->save();

            // $order->id only work when it saved
            $orderKey = 'OID-' . $order->id . '-' . time();
            $order->key = $orderKey;

            $promoCode = PromoCode::where('code', $promoCodeValue)->first();
            if ($promoCode) {
                $promoUser = new PromoUser();
                $promoUser->user_id = $packageBuyerUser->id;
                $promoUser->order_id = $order->id;
                $promoUser->promo_code_id = $promoCode->id;
                $promoUser->code = $promoCode->code;
                $promoUser->promo_code_data = $promoCode->toJson();
                $promoUser->save();

                // Cut down discount from order
                $order->promo_discount = $chargeInfo['promoDiscount'];
            }

            // Save order information after change
            $order->save();

            $quickpayClientService = new QuickpayClientService();
            $client = $quickpayClientService->getClient();

            // Create payment
            $payment = $client->request->post('/payments', [
                'order_id' => $orderKey,
                'currency' => $currencyCode,
            ]);

            $status = $payment->httpStatus();

            // Determine if payment was created successfully
            if ($status === 201) {
                $paymentObject = $payment->asObject();

                // Construct url to create payment link
                $endpoint = sprintf("/payments/%s/link", $paymentObject->id);

                // Issue a put request to create payment link
                $modifiedContinueUrl = $continueUrl . "&quick_booking=${isQuickBooking}&order_key=${orderKey}&sale_price=${salePrice}";
                $linkRequest = $client->request->put($endpoint, [
                    'amount' => $totalAmount * 100,
                    'continue_url' => $isQuickBooking ? $modifiedContinueUrl : $continueUrl,
                    'cancel_url' => $cancelUrl,
                    'auto_capture' => $isQuickBooking ? true : false
                ]);

                //Determine if payment link was created succesfully
                if ($linkRequest->httpStatus() === 200) {

                    // Store payment information
                    $payment = new Payment();
                    $payment->order_id = $order->id;
                    $payment->details = json_encode(['payment_id' => $paymentObject->id]);
                    $payment->authorization_link = $linkRequest->asObject()->url;
                    $payment->service_provider = 'QuickPay';
                    $payment->method = $paymentMethod;
                    $payment->save();

                    $contactService->create($packageOwnerUser, $packageBuyerUser);

                    DB::commit();

                    // Mail to administrator
                    if ($isQuickBooking) {
                        Mail::to([config('mail.from.address')])->queue(new NewOrderCapture($order));
                    }

                    return response()->json(
                        [
                            'bookingId' => $booking->id,
                            'link' => $linkRequest->asObject()->url
                        ]
                    );
                }
            } else {
                throw new Exception('Something wrong, booking order is not working correctly.');
            }

        } catch (Exception $e) {
            DB::rollBack();

            if ($e instanceof ValidationException) {
                return response()->json(
                    $e->validator->errors()->first(),
                    StatusCode::HTTP_UNPROCESSABLE_ENTITY
                );
            }

            return response()->json([
                'message' => $e->getMessage(),
                'line' => $e->getLine()
            ], StatusCode::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function notify(Request $request)
    {
        $bookingId = $request->bookingId;
        try {
            $booking = Booking::find($bookingId);
            if (!$booking) {
                throw new Exception('Sorry, we can not notify to the coach');
            }

            $bookingService = new BookingService();
            $contactService = new ContactService();

            $packageBuyerUser = $booking->packageBuyerUser;
            $packageOwnerUser = $booking->packageOwnerUser;
            $order = $booking->order;

            // Update initial bookings
            $initialBookings = Booking::where(function ($q) use ($packageBuyerUser, $packageOwnerUser) {
                $q->where('package_owner_user_id', $packageOwnerUser->id);
                $q->where('package_buyer_user_id', $packageBuyerUser->id);
            })->where('status', 'Initial')->get();
            if ($initialBookings->count() > 0) {
                $bookingService->checkPaymentStatusOfInitialBookings($initialBookings);
                $contactService->resetContactNewMessageCount($packageBuyerUser, $packageOwnerUser);
            }

            if ($booking->is_quick_booking) {
                Mail::to($packageOwnerUser)->send(new CoachPackageConfirmation($booking));
                Mail::to($packageBuyerUser)->send(new AthletePackageConfirmation($booking));
            } else {
                Mail::to($packageBuyerUser)->send(new AthletePendingPackageRequest($packageBuyerUser, $order));
                Mail::to($packageOwnerUser)->send(new CoachPendingPackageRequest($packageOwnerUser, $packageBuyerUser, $order));
            }

        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], StatusCode::HTTP_UNPROCESSABLE_ENTITY);
        }

    }
}
