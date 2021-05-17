<?php


namespace Coachsome\BaseReview\Mail;


use App\Entities\User;
use App\Services\TranslationService;
use Coachsome\BaseReview\Models\BaseReview;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

class CoachNotification extends Mailable
{
    use Queueable, SerializesModels;

    private $baseReview = null;
    private $translationService = [];

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(BaseReview $baseReview)
    {
        $this->baseReview = $baseReview;
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
        $coachName = "";
        $reviewerName = "";
        $reviewerText = null;
        $seeReviewUrl = null;


        $translations = $this->translationService->getKeyByLanguageCode(App::getLocale());
        $coachUser = User::find($this->baseReview->user_id);
        $reviewerUser = User::find($this->baseReview->reviewer_id);
        $reviewerText = $this->baseReview->text;

        $profile = $coachUser->profile;
        if($profile){
            $coachName = $profile->profile_name;
        }

        if($reviewerUser){
            $reviewerName = $reviewerUser->first_name. " ". $reviewerUser->last_name;
        }

        $emailData = [
            'translations' => $translations,
            'reviewerName' => $reviewerName,
            'coachName' => $coachName,
            'reviewerText' => $reviewerText,
            'seeReviewUrl' => env('APP_CLIENT_DOMAIN')."/".$coachUser->user_name,
            'termsUrl' => env('APP_CLIENT_DOMAIN_TERMS_PAGE'),
            'clientHomeUrl' => env('APP_CLIENT_DOMAIN'),
            'coachsomeLinkedinUrl' => "https://www.linkedin.com/company/coachsome/",
            'coachsomeFacebookUrl' => "https://www.facebook.com/coachsome/app/212104595551052/?ref=page_internal",
            'linkedinIconUrl' => env('APP_SERVER_DOMAIN_PUBLIC_PATH') . '/assets/images/icons/linkedin.png',
            'facebookIconUrl' => env('APP_SERVER_DOMAIN_PUBLIC_PATH') . '/assets/images/icons/facebook.png',
            'logoIconUrl' => env('APP_SERVER_DOMAIN_PUBLIC_PATH') . '/assets/images/icons/logo.png',
            'logoUrl' => env('APP_SERVER_DOMAIN_PUBLIC_PATH') . '/assets/images/logos/logo.png',
        ];
        return $this->view('baseReview::coachNotification')
            ->subject("Review Notification")
            ->with($emailData);
    }
}
