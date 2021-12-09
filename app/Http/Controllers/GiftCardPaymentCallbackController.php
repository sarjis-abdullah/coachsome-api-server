<?php

namespace App\Http\Controllers;

use App\Entities\GiftOrder;
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

        if ($giftOrder) {
            $user = User::find($giftOrder->user_id);
            $promoCode = PromoCode::find($giftOrder->promo_code_id);
            if ($promoCode) {
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

                $pdf = PDF::setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true])->loadView('emails.giftCard', $data);
                Mail::send('emails.giftCard', $data, function ($message) use ($data, $pdf) {
                    $message->to($data["email"], $data["email"])
                        ->subject($data["title"])
                        ->attachData($pdf->output(), "gift-card.pdf");
                });
            }
        }
        // return Redirect::to(config('company.url.gift_checkout_page'));
    }

    public function cancel()
    {
        return redirect(config('company.url.gift_checkout_page'));
    }
}
