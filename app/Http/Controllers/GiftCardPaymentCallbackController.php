<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;

class GiftCardPaymentCallbackController extends Controller
{
    public function continue()
    {
        return Redirect::to(config('company.url.gift_checkout_page'));
    }

    public function cancel()
    {
        return redirect(config('company.url.gift_checkout_page'));
    }
}
