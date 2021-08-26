<?php

namespace App\Mail;

use App\Services\CurrencyService;
use App\Services\TranslationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;

class NewOrderCapture extends Mailable
{
    use Queueable, SerializesModels;

    private $order;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($order)
    {
        $this->order = $order;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {

        $packageName = '';
        $customerName= '';
        $coachName = '';

        $translationService = new TranslationService();
        $currencyService = new CurrencyService();

        $packageSnapshot = $this->order ? json_decode($this->order->package_snapshot) : null;
        $booking = $this->order->booking;

        if($booking){
            $packageOwnerUser = $booking->packageOwnerUser;
            $packageBuyerUser = $booking->packageBuyerUser;
            $coachName = $packageOwnerUser ? $packageOwnerUser->first_name. ' '.$packageOwnerUser->last_name : '';
            $customerName = $packageBuyerUser ? $packageBuyerUser->first_name. ' '.$packageBuyerUser->last_name : '';
        }
        $translations = $translationService->getKeyByLanguageCode(App::getLocale());
        if ($packageSnapshot) {
            $packageName = $packageSnapshot->details ? $packageSnapshot->details->title : '';
        }

        $emailData = [
            'translations' => $translations,
            'packageName' => $packageName,
            'orderId' => $this->order->key,
            'customerName' => $customerName,
            'coachName' => $coachName,
            'packageName' => $packageName,
            'price' => $currencyService->format($this->order->total_amount, $this->order->currency),
            'orderListUrl' => config('company.url.order_list_page'),
            'coachsomeEmailAddress' => config('mail.from.address'),
            'termsUrl' => config("company.url.terms_page"),
            'clientHomeUrl' => config("company.url.home_page"),
            'coachsomeLinkedinUrl' => config("company.url.linkedin"),
            'coachsomeFacebookUrl' => config("company.url.facebook"),
            'linkedinIconUrl' => config("company.url.linkedin_icon"),
            'facebookIconUrl' => config("company.url.facebook_icon"),
            'logoIconUrl' => config("company.url.logo_icon"),
            'logoUrl' => config("company.url.logo"),
        ];
        return $this->view('emails.newOrderCapture')
            ->with($emailData);
    }
}
