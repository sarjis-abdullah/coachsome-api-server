<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;

class GiftCardPaymentCallbackController extends Controller
{
    public function continue()
    {
        $pdf = PDF::setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true])->loadView('emails.giftCard', []);
        $data["email"] = "aatmaninfotech@gmail.com";
        $data["title"] = "Gift Card";
        $data["body"] = "This is Demo";

        Mail::send('emails.giftCard', $data, function ($message) use ($data, $pdf) {
            $message->to($data["email"], $data["email"])
                ->subject($data["title"])
                ->attachData($pdf->output(), "gift-card.pdf");
        });

        // return Redirect::to(config('company.url.gift_checkout_page'));
    }

    public function cancel()
    {
        return redirect(config('company.url.gift_checkout_page'));
    }
}
