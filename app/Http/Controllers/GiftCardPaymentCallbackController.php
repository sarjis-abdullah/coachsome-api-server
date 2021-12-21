<?php

namespace App\Http\Controllers;

use App\Data\OrderStatus;
use App\Entities\GiftOrder;
use App\Entities\GiftPayment;
use App\Entities\PromoCode;
use App\Entities\User;
use App\Services\TranslationService;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\Request;

class GiftCardPaymentCallbackController extends Controller
{
    public function continue(Request $request)
    {

        $id = $request->query('id');
        $giftOrder = GiftOrder::find($id);

        if ($giftOrder && $giftOrder->status != OrderStatus::CAPTURE) {
            $user = User::find($giftOrder->user_id);
            $promoCode = PromoCode::find($giftOrder->promo_code_id);
            if ($promoCode) {
                $giftOrder->status = OrderStatus::CAPTURE;
                $giftOrder->save();
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
                $data["recipentMessage"] = $giftOrder->message;

                $pdf = PDF::setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true])->loadView('emails.giftCard', $data);
                Mail::send('emails.giftCard', $data, function ($message) use ($data, $pdf) {
                    $message->to($data["email"], $data["email"])
                        ->subject($data["title"])
                        ->attachData($pdf->output(), "gift-card.pdf");
                });
            }
            return Redirect::to(config('company.url.gift_checkout_page')."?id=".$id."&status=success");
        } else {
            return Redirect::to(config('company.url.gift_page'));
        }
    }

    public function cancel(Request $request)
    {
        $id = $request->query('id');
        $giftOrder = GiftOrder::find($id);
        $giftOrder->status = OrderStatus::CANCELED;
        if($giftOrder){
            $promoCode = PromoCode::find($giftOrder->promo_code_id);
            $promoCode->delete();
        }
        return redirect(config('company.url.gift_page'));
    }
}
