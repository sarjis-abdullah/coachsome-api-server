<?php

namespace App\Mail;

use App\Services\CurrencyService;
use App\Services\TranslationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

class AthleteDeclinedPackage extends Mailable
{
    use Queueable, SerializesModels;

    private $booking = null;
    private $translationService;
    private $currencyService;

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
        $translations = null;

        $packageOwnerUser = $this->booking->packageOwnerUser;
        $packageBuyerUser = $this->booking->packageBuyerUser;
        $order = $this->booking->order;
        $packageSnapshot = $order ? json_decode($order->package_snapshot) : null;
        $translations = $this->translationService->getKeyByLanguageCode(App::getLocale());

        if ($packageSnapshot) {
            $packageName = $packageSnapshot->details ? $packageSnapshot->details->title : '';
        }

        if ($order) {
            $orderId = $order->key;
        }


        $emailData = [
            'packageName' => $packageName,
            'orderId' => $orderId,
            'packageOwnerName' => $packageOwnerUser->first_name.' '.$packageOwnerUser->last_name,
            'packageBuyerName' => $packageBuyerUser->first_name.' '.$packageBuyerUser->last_name,
            'translations' => $translations,
            'marketplacePageUrl' => env('APP_CLIENT_DOMAIN_MARKETPLACE_PAGE'),
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
        return $this->view('emails.athleteDeclinedPackage')
            ->with($emailData);
    }
}
