<?php

namespace App\Mail;

use App\Services\TranslationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;

/**
 * This email sent after athlete buy package
 */
class CoachPendingPackageRequest extends Mailable
{
    use Queueable, SerializesModels;

    private $packageBuyerUser;
    private $packageOwnerUser;
    private $order;
    private $translationService;


    /**
     * Create a new message instance.
     *
     * @param $user
     */
    public function __construct($packageOwnerUser,$packageBuyerUser, $order)
    {
        $this->packageOwnerUser = $packageOwnerUser;
        $this->packageBuyerUser = $packageBuyerUser;
        $this->order = $order;
        $this->translationService = new TranslationService();

    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $emailData = [
            'translation' => $this->translationService->getKeyByLanguageCode(App::getLocale()),
            'packageOwnerName' => $this->packageOwnerUser->first_name . ' ' . $this->packageOwnerUser->last_name,
            'packageBuyerName' => $this->packageBuyerUser->first_name . ' ' . $this->packageBuyerUser->last_name,
            'packageName' => json_decode($this->order->package_snapshot)->details->title,
            'orderId' => $this->order->key,
            'termsUrl' => env('APP_CLIENT_DOMAIN_TERMS_PAGE'),
            'clientHomeUrl' => env('APP_CLIENT_DOMAIN'),
            'coachsomeLinkedinUrl' => "https://www.linkedin.com/company/coachsome/",
            'coachsomeFacebookUrl' => "https://www.facebook.com/coachsome/app/212104595551052/?ref=page_internal",
            'coachsomeEmailAddress' => "info@coachsome.com",
            'linkedinIconUrl' => env('APP_SERVER_DOMAIN_PUBLIC_PATH') . '/assets/images/icons/linkedin.png',
            'facebookIconUrl' => env('APP_SERVER_DOMAIN_PUBLIC_PATH') . '/assets/images/icons/facebook.png',
            'logoIconUrl' => env('APP_SERVER_DOMAIN_PUBLIC_PATH') . '/assets/images/icons/logo.png',
            'logoUrl' => env('APP_SERVER_DOMAIN_PUBLIC_PATH') . '/assets/images/logos/logo.png',
            'bookingPageUrl' => env('APP_CLIENT_DOMAIN_COACH_BOOKING_PAGE'),
        ];

        return $this->view('emails.coachPendingPackageRequest')
            ->with($emailData);

    }
}
