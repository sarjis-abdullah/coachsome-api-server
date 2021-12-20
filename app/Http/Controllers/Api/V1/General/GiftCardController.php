<?php

namespace App\Http\Controllers\Api\V1\General;

use App\Data\OrderStatus;
use App\Data\Promo;
use App\Data\ServiceProviderData;
use App\Data\StatusCode;
use App\Entities\GiftOrder;
use App\Entities\GiftPayment;
use App\Entities\PromoCode;
use App\Entities\User;
use App\Http\Controllers\Controller;
use App\Services\CurrencyService;
use App\Services\QuickpayClientService;
use App\Services\TokenService;
use App\Services\TranslationService;
use Barryvdh\DomPDF\Facade as PDF;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GiftCardController extends Controller
{
    public function pay(Request $request)
    {

        try {

            $request->validate([
                'currency' => 'required',
                'totalAmount' => 'nullable|numeric',
                'paymentMethod' => 'required',
                'message' => 'nullable',
                'recipentName' => 'required'
            ]);

            $cancelUrl = route('gift-cards.payments.cancel');
            $continueUrl = route('gift-cards.payments.continue');

            $currencyService  = new CurrencyService();
            $tokenService  = new TokenService();
            $currency = $currencyService->getByCode($request['currency']);
            if (!$currency) {
                throw new Exception("Currecny is not found");
            }

            $token = substr(md5(time()), 0, 8);

            DB::beginTransaction();

            $promoCode = new PromoCode();
            $promoCode->code = $token;
            $promoCode->name = $token;
            $promoCode->promo_type_id = Promo::TYPE_ID_FIXED;
            $promoCode->promo_duration_id = Promo::DURATION_ID_ONCE;
            $promoCode->currency_id = $currency->id;
            $promoCode->discount_amount = $request['totalAmount'];
            $promoCode->save();

            // Creating order
            $order = new GiftOrder();
            $order->user_id = Auth::id();
            $order->promo_code_id = $promoCode->id;
            $order->currency = $currency->code;
            $order->message = $request['message'];
            $order->recipent_name = $request['recipentName'];
            $order->total_amount = $request['totalAmount'];
            $order->status = OrderStatus::INITIAL;
            $order->order_date = Carbon::now();
            $order->save();

            // $order->id only work when it saved
            $orderKey = $tokenService->getUniqueId('G');
            $order->key = $orderKey;
            $order->save();


            $quickpayClientService = new QuickpayClientService();
            $client = $quickpayClientService->getClient();

            // Create payment
            $payment = $client->request->post('/payments', [
                'order_id' => $orderKey,
                'currency' => $currency->code,
            ]);

            $status = $payment->httpStatus();

            if ($status === 201) {
                $paymentObject = $payment->asObject();
                $endpoint = sprintf("/payments/%s/link", $paymentObject->id);
                $linkRequest = $client->request->put($endpoint, [
                    'amount' => $request['totalAmount'] * 100,
                    'continue_url' => $continueUrl . '?id=' . $order->id,
                    'cancel_url' => $cancelUrl. '?id=' . $order->id,
                    'auto_capture' => true
                ]);

                if ($linkRequest->httpStatus() === 200) {
                    // Store payment information
                    $payment = new GiftPayment();
                    $payment->gift_order_id = $order->id;
                    $payment->details = json_encode(['payment_id' => $paymentObject->id]);
                    $payment->authorization_link = $linkRequest->asObject()->url;
                    $payment->service_provider = ServiceProviderData::NAME_QUICKPAY;
                    $payment->method = $request['paymentMethod'];
                    $payment->save();
                    DB::commit();
                }
            }

            return response([
                'data' => [
                    'link' => $linkRequest->asObject()->url
                ]
            ], StatusCode::HTTP_OK);
        } catch (\Exception $e) {
            if ($e instanceof ValidationException) {
                return response([
                    'error' => [
                        'message' => $e->validator->errors()->first()
                    ]
                ], StatusCode::HTTP_UNPROCESSABLE_ENTITY);
            }
            return response([
                'error' => [
                    'message' => $e->getMessage()
                ]
            ], StatusCode::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function  downloadGiftCard(Request $request, $id)
    {
        try {
            $giftOrder = GiftOrder::find($id);
            if (!$giftOrder) {
                throw new Exception("Gift order is not found.");
            }

            $promoCode = PromoCode::find($giftOrder->promo_code_id);
            if (!$promoCode) {
                throw new Exception('Promo code is not found');
            }

            $user = User::find($giftOrder->user_id);
            $translationService = new TranslationService();
            $translations = $translationService->getKeyByLanguageCode(App::getLocale());
            $data["title"] = "Gift Card";
            $data["email"] = $user->email;
            $data["translations"] = $translations;
            $data["firstName"] = $user->first_name;
            $data["lastName"] = $user->last_name;
            $data["code"] = $promoCode->code;
            $data["value"] = $giftOrder->total_amount;
            $data["currency"] = $giftOrder->currency;
            $data["recipentName"] = $giftOrder->recipent_name;
            $pdf = PDF::setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true])->loadView('emails.giftCard', $data);
            return $pdf->download();

        } catch (\Exception $e) {
            if ($e instanceof ValidationException) {
                return response([
                    'error' => [
                        'message' => $e->validator->errors()->first()
                    ]
                ], StatusCode::HTTP_UNPROCESSABLE_ENTITY);
            }
            return response([
                'error' => [
                    'message' => $e->getMessage()
                ]
            ], StatusCode::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
