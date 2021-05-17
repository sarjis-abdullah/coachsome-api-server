<?php

namespace App\Mail;

use App\Services\CurrencyService;
use App\Services\OrderService;
use App\Services\TranslationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

class AthletePackageConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    private $booking = null;
    private $translationService;
    private $currencyService;
    private $orderService;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($booking)
    {
        $this->booking = $booking;
        $this->translationService = new TranslationService();
        $this->currencyService = new CurrencyService();
        $this->orderService = new OrderService();
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $packageName = '';
        $orderId = '';
        $chatNowUrl = null;

        $packagePrice = 0;
        $packageTotalPrice = 0;
        $serviceFee = 0.00;
        $vat = 0.00;
        $grandTotal = 0.00;

        $packageQty = 1;
        $serviceFeeQty = 1;

        $translations = null;
        $paymentReceiveDate = date('d-m-Y');

        $packageOwnerUser = $this->booking->packageOwnerUser;
        $packageBuyerUser = $this->booking->packageBuyerUser;
        $order = $this->booking->order;
        $packageSnapshot = $order ? json_decode($order->package_snapshot) : null;
        $translations = $this->translationService->getKeyByLanguageCode(App::getLocale());

        if ($this->booking->date_of_confirmation) {
            $paymentReceiveDate = date('d-m-Y', strtotime($this->booking->date_of_confirmation));
        }

        if ($packageSnapshot) {
            $packageName = $packageSnapshot->details ? $packageSnapshot->details->title : '';
        }

        if ($order) {
            $orderId = $order->key;
            $packageTotalPrice = $this->currencyService->format($this->orderService->totalPrice($order) , $order->currency);
            $serviceFee = $this->currencyService->format($this->orderService->serviceFee($order), $order->currency);
            $grandTotal = $this->currencyService->format($this->orderService->grandTotal($order), $order->currency);
            $vat = $this->currencyService->format($this->orderService->vat($order), $order->currency);
            $packageQty = $this->orderService->packageQty($order);
            $serviceFeeQty = $this->orderService->serviceFeeQty($order);
        }


        $emailData = [
            'packageName' => $packageName,
            'orderId' => $orderId,
            'packageBuyerName' => $packageBuyerUser->first_name . ' ' . $packageBuyerUser->last_name,

            'packageQty' => $packageQty,
            'serviceFeeQty' => $serviceFeeQty,
            'packagePrice' => $packageTotalPrice,
            'serviceFee' => $serviceFee,
            'grandTotal' => $grandTotal,
            'vat' => $vat,

            'paymentReceiveDate' => $paymentReceiveDate,

            'translations' => $translations,

            'chatNowUrl' => env('APP_CLIENT_DOMAIN_CHAT_PAGE') . '?userId=' . $packageOwnerUser->id,
            'coachsomeEmailAddress' => config('mail.from.address'),
            'termsUrl' => env('APP_CLIENT_DOMAIN_TERMS_PAGE'),
            'clientHomeUrl' => env('APP_CLIENT_DOMAIN'),
            'coachsomeLinkedinUrl' => "https://www.linkedin.com/company/coachsome/",
            'coachsomeFacebookUrl' => "https://www.facebook.com/coachsome/app/212104595551052/?ref=page_internal",
            'linkedinIconUrl' => env('APP_SERVER_DOMAIN_PUBLIC_PATH') . '/assets/images/icons/linkedin.png',
            'facebookIconUrl' => env('APP_SERVER_DOMAIN_PUBLIC_PATH') . '/assets/images/icons/facebook.png',
            'logoIconUrl' => env('APP_SERVER_DOMAIN_PUBLIC_PATH') . '/assets/images/icons/logo.png',
            'logoUrl' => env('APP_SERVER_DOMAIN_PUBLIC_PATH') . '/assets/images/logos/logo.png',
        ];
        return $this->view('emails.athletePackageConfirmation')
            ->with($emailData);
    }
}
