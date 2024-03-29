<?php

namespace App\Http\Controllers\Api\V1\General;

use App\Data\CurrencyCode;
use App\Data\OrderStatus;
use App\Data\SettingValue;
use App\Data\StatusCode;
use App\Data\TransactionType;
use App\Entities\NotificationSetting;
use App\Entities\PaymentCard;
use App\Entities\PromoUser;
use App\Entities\Booking;
use App\Entities\BookingSetting;
use App\Entities\Contact;
use App\Entities\GiftTransaction;
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
use App\Services\ProfileService;
use App\Services\Promo\PromoService;
use App\Services\QuickpayCardService;
use App\Services\QuickpayClientService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
                'promoCode' => 'nullable',
                'useGiftCard' => 'nullable',
                'paymentMethod' => 'nullable'
            ]);

            $packageId = $request->packageId;
            $localCurrency = $request->header('Currency-Code');
            $packageBuyerMessage = $request->message;
            $numberOfAttendees = $request->numberOfAttendees;
            $salePrice = $request->salePrice;
            $paymentMethod = $request->paymentMethod;
            $promoCodeValue = $request->promoCode;
            $useGiftCard = $request->useGiftCard;
            $continueUrl = $request->packageUrl . "?payment_status=paid&promo=".$promoCodeValue;
            $cancelUrl = $request->packageUrl . "?payment_status=cancel";

            $package = Package::with(['category', 'details', 'user'])
                ->where('id', $packageId)
                ->first();


            if (!$paymentMethod && !$useGiftCard) {
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
            $packageService = new PackageService();
            $currencyService = new CurrencyService();

            // Set fallback currency
            if (!$localCurrency) {
                $localCurrency = $currencyService->getDefaultBasedCurrency()->code;
            }

            $packageOwnerPackageSetting = $packageOwnerUser->ownPackageSetting;
            $isQuickBooking = $packageOwnerPackageSetting->is_quick_booking ?? false;


            DB::beginTransaction();

            // Booking setting
            $bookingSetting = BookingSetting::get()->first();

            // Create booking
            $booking = new Booking();
            $booking->package_owner_user_id = $packageOwnerUser->id;
            $booking->package_buyer_user_id = $packageBuyerUser->id;
            $booking->sender_user_role = $packageOwnerUser->roles[0]->name;
            $booking->receiver_user_role = $packageBuyerUser->roles[0]->name;
            $booking->package_buyer_message = $packageBuyerMessage;
            $booking->booking_settings_snapshot = $bookingSetting->toJson();
            $booking->package_owner_service_fee_snapshot = $bookingSetting->package_owner_gnr_service_fee;
            $booking->package_buyer_service_fee_snapshot = $bookingSetting->package_buyer_service_fee;
            $booking->hereof_vat_snapshot = $bookingSetting->hereof_vat;
            $booking->booking_date = Carbon::now();
            $booking->is_quick_booking = $isQuickBooking;
            $booking->save();

            // Package charge info
            $chargeInfo = $packageService->chargeInformation(
                $package,
                CurrencyCode::DANISH_KRONER,
                [
                    'promoCode' => $promoCodeValue,
                    'packageBuyerUser' => $packageBuyerUser,
                    'useGiftCard' => $request['useGiftCard']
                ]
            );

            // Save gift transaction amount
            $giftTransaction = null;
            if ($chargeInfo['giftCard']['payableAmount']) {
                $giftTransaction = new GiftTransaction();
                $giftTransaction->user_id = Auth::id();
                $giftTransaction->gift_order_id = null;
                $giftTransaction->transaction_date = Carbon::now();
                $giftTransaction->amount = $chargeInfo['giftCard']['payableAmount'];
                $giftTransaction->currency = CurrencyCode::DANISH_KRONER;
                $giftTransaction->type = TransactionType::CREDIT;
                $giftTransaction->save();
            }

            // Create order
            // The order currency is default base currency
            $order = new Order();
            $order->booking_id = $booking->id;
            $order->package_id = $package->id;
            $order->gift_transaction_id = $giftTransaction ? $giftTransaction->id : null;
            $order->gift_card_amount = $giftTransaction ? $giftTransaction->amount : 0.00;
            $order->package_category_id = $packageCategory->id;
            $order->package_snapshot = $package->toJson();
            $order->number_of_attendees = $numberOfAttendees;
            $order->package_sale_price = $chargeInfo['salePrice'];
            $order->total_per_person = $chargeInfo['totalPerPerson'];
            $order->currency = CurrencyCode::DANISH_KRONER;
            $order->total_amount = $chargeInfo['total'];
            $order->service_fee = $chargeInfo['serviceFee'];
            $order->status = OrderStatus::INITIAL;
            $order->transaction_date = Carbon::now();
            $order->save();

            // Order needs to save multiple time
            // because order id is not found until it is saved
            $orderKey = 'OID-' . $order->id . '-' . time();
            $order->key = $orderKey;
            $order->save();

            // If order total amount is equal to 0 then it is not required to pay by quickpay
            if ($order->total_amount < 1) {
                $contactService->create($packageOwnerUser, $packageBuyerUser);

                DB::commit();
                Mail::to([config('mail.from.address')])->queue(new NewOrderCapture($order));
                return response([], StatusCode::HTTP_OK);
            }

            // If order has total amount value then it should be paid by quickpay
            if ($order->total_amount > 0) {
                $quickpayClientService = new QuickpayClientService();
                $client = $quickpayClientService->getClient();

                // This charge info contains local currency of the user
                // because user only pay by their local currency
                // Quickpay payment currency will be the user local currency
                $chargeInfo = $packageService->chargeInformation(
                    $package,
                    $localCurrency,
                    [
                        'promoCode' => $promoCodeValue,
                        'packageBuyerUser' => $packageBuyerUser,
                        'useGiftCard' => $request['useGiftCard']
                    ]
                );

                // Local currency is user currency
                // Local total amount that paid by quickpay
                $order->local_currency = $localCurrency;
                $order->local_total_amount = $chargeInfo['total'];
                $order->save();

                // Create payment
                $payment = $client->request->post('/payments', [
                    'order_id' => $orderKey,
                    'currency' => $localCurrency,
                ]);

                $status = $payment->httpStatus();

                // Determine if payment was created successfully
                if ($status === 201) {
                    $useSavedCard = false;
                    $link = null;
                    $paymentObject = $payment->asObject();
                    $requestStatus = null;
                    $modifiedContinueUrl = $continueUrl . "&quick_booking=${isQuickBooking}&order_key=${orderKey}&sale_price=${salePrice}";
                    // $profile = $packageBuyerUser->profile;
                    $payload = [
                        'amount' => ($request['numberOfAttendees'] * $chargeInfo['total']) * 100, //$chargeInfo['total'] * 100,
                        'continue_url' => $isQuickBooking ? $modifiedContinueUrl : $continueUrl,
                        'cancel_url' => $cancelUrl,
                        'payment_methods' => $paymentMethod,
                        // 'invoice_address[mobile_number]' = $profile = $user->profile;
                        'auto_capture' => $isQuickBooking ? true : false,
                    ];

                    // if($paymentMethod == 'mpobilepay' || $paymentMethod == 'mobile_pay'){
                    //     $profileService = new ProfileService();
                    //     $payload = [
                    //         'amount' => ($request['numberOfAttendees'] * $chargeInfo['total']) * 100, //$chargeInfo['total'] * 100,
                    //         'continue_url' => $isQuickBooking ? $modifiedContinueUrl : $continueUrl,
                    //         'cancel_url' => $cancelUrl,
                    //         'payment_methods' => $paymentMethod,
                    //         'invoice_address[mobile_number]' =>$profileService->getPhoneNumber($profile),
                    //         'auto_capture' => $isQuickBooking ? true : false,
                    //     ];
                    // }

                    // If the user saved any card then he should pay as a default method
                    // otherwise he has to generate a link to authorize
                    $mPaymentCard = PaymentCard::where('user_id', $packageBuyerUser->id)
                        ->where("brand", $paymentMethod)
                        ->first();
                    if ($mPaymentCard) {
                        $quickpayCardService = new QuickpayCardService();
                        $paymentCardObj = $quickpayCardService->getQuickPayCard($mPaymentCard->card_id)->asObject();
                        if ($paymentCardObj) {
                            $useSavedCard = true;
                            $cardToken = $quickpayCardService->getCardToken($mPaymentCard->card_id);
                            if ($cardToken) {
                                $payload['card'] = [
                                    'token' => $cardToken
                                ];
                                $authorizeRequest = $client->request->post(
                                    sprintf("/payments/%s/authorize", $paymentObject->id),
                                    $payload
                                );
                                $requestStatus = $authorizeRequest->httpStatus();
                            }

                        }
                    } else {
                        // Issue a put request to create payment link
                        $linkRequest = $client->request->put(sprintf("/payments/%s/link", $paymentObject->id), $payload);
                        $requestStatus = $linkRequest->httpStatus();
                        $link = $linkRequest->asObject()->url;
                    }

                    //Determine if payment link was created succesfully
                    if ($requestStatus == 200 || $requestStatus == 202) {
                        $payment = new Payment();
                        $payment->order_id = $order->id;
                        $payment->details = json_encode(['payment_id' => $paymentObject->id]);
                        $payment->authorization_link = $link;
                        $payment->service_provider = 'QuickPay';
                        $payment->method = $paymentMethod;
                        $payment->save();

                        $contactService->create($packageOwnerUser, $packageBuyerUser);

                        DB::commit();

                        if ($isQuickBooking) {
                            // Mail to administrator
                            Mail::to([config('mail.from.address')])->queue(new NewOrderCapture($order));
                        }

                        // Promo code value is a discount amount
                        $promoCode = PromoCode::where('code', $promoCodeValue)->first();
                        
                        if ($promoCode) {
                        //     // Save the promo user
                        //     $promoUser = new PromoUser();
                        //     $promoUser->user_id = $packageBuyerUser->id;
                        //     $promoUser->order_id = $order->id;
                        //     $promoUser->promo_code_id = $promoCode->id;
                        //     $promoUser->code = $promoCode->code;
                        //     $promoUser->promo_code_data = $promoCode->toJson();
                        //     $promoUser->save();

                        //     // Cut down discount from order
                        //     // Save order information after change
                            $order->promo_discount = $chargeInfo['promoDiscount'];
                            $order->save();
                        }

                        return response([
                            'useSavedCard' => $useSavedCard,
                            'bookingId' => $booking->id,
                            'link' => $link
                        ],
                            StatusCode::HTTP_OK
                        );
                    } else {
                        throw new \Exception("Request is not accepted successfully");
                    }
                }
            }
        } catch (Exception $e) {
            DB::rollBack();
            if ($e instanceof ValidationException) {
                return response()->json(
                    $e->validator->errors()->first(),
                    StatusCode::HTTP_UNPROCESSABLE_ENTITY
                );
            }
            return response([
                'message' => $e->getMessage(),
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

            $packageOwnerNotificationSetting = NotificationSetting::where('user_id', $packageOwnerUser->id)->first();
            $packageBuyerNotificationSetting = NotificationSetting::where('user_id', $packageBuyerUser->id)->first();


            // Promo code value is a discount amount
            if($request->promoCodeValue != ""){
                $promoCode = PromoCode::where('code', $request->promoCodeValue)->first();
                // $packageBuyerUser = Auth::user();
                if ($promoCode) {
                    // Save the promo user
                    $promoUser = new PromoUser();
                    $promoUser->user_id = $packageBuyerUser->id;
                    $promoUser->order_id = $order->id;
                    $promoUser->promo_code_id = $promoCode->id;
                    $promoUser->code = $promoCode->code;
                    $promoUser->promo_code_data = $promoCode->toJson();
                    $promoUser->save();
                }
            }
            

            if ($booking->is_quick_booking) {
                // Before sending email notification you have to check setting
                if ($packageOwnerNotificationSetting &&
                    $packageOwnerNotificationSetting->order_message == SettingValue::ID_EMAIL) {
                    Mail::to($packageOwnerUser)->send(new CoachPackageConfirmation($booking));
                }
                if ($packageBuyerNotificationSetting &&
                    $packageBuyerNotificationSetting->order_message == SettingValue::ID_EMAIL) {
                    Mail::to($packageBuyerUser)->send(new AthletePackageConfirmation($booking));
                }
            } else {
                if ($packageOwnerNotificationSetting &&
                    $packageOwnerNotificationSetting->order_message == SettingValue::ID_EMAIL) {
                    Mail::to($packageOwnerUser)->send(new CoachPendingPackageRequest($packageOwnerUser, $packageBuyerUser, $order));
                }
                if ($packageBuyerNotificationSetting &&
                    $packageBuyerNotificationSetting->order_message == SettingValue::ID_EMAIL) {
                    Mail::to($packageBuyerUser)->send(new AthletePendingPackageRequest($packageBuyerUser, $order));
                }
            }
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], StatusCode::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
