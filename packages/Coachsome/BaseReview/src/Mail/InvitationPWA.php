<?php


namespace Coachsome\BaseReview\Mail;


use App\Entities\User;
use App\Services\TranslationService;
use Coachsome\BaseReview\Models\BaseReview;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class InvitationPWA extends Mailable
{
    use Queueable, SerializesModels;

    private $translationService = [];

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->translationService = new TranslationService();
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $translations = null;
        $requesterName = "";
        $userName = "";

        $translations = $this->translationService->getKeyByLanguageCode(App::getLocale());
        $authUser = Auth::user();

        $profile = $authUser->profile;
        if ($profile) {
            $requesterName = $profile->profile_name;
        }

        if ($authUser) {
            $userName = $authUser->user_name;
        }

        $emailData = [
            'translations' => $translations,
            'requesterName' => $requesterName,
            'userName' => $userName,
            'reviewUrl' => str_replace('username', $userName, env('APP_PWA_DOMAIN_BASE_REVIEW_URL')),
            'termsUrl' => env('APP_PWA_DOMAIN_TERMS_PAGE'),
            'clientHomeUrl' => env('APP_PWA_DOMAIN'),
            'coachsomeLinkedinUrl' => "https://www.linkedin.com/company/coachsome/",
            'coachsomeFacebookUrl' => "https://www.facebook.com/coachsome/app/212104595551052/?ref=page_internal",
            'linkedinIconUrl' => env('APP_SERVER_DOMAIN_PUBLIC_PATH') . '/assets/images/icons/linkedin.png',
            'facebookIconUrl' => env('APP_SERVER_DOMAIN_PUBLIC_PATH') . '/assets/images/icons/facebook.png',
            'logoIconUrl' => env('APP_SERVER_DOMAIN_PUBLIC_PATH') . '/assets/images/icons/logo.png',
            'logoUrl' => env('APP_SERVER_DOMAIN_PUBLIC_PATH') . '/assets/images/logos/logo.png',
        ];
        return $this->view('baseReview::invitation')
            ->subject("Review Request")
            ->with($emailData);
    }
}
