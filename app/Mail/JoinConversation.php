<?php

namespace App\Mail;

use App\Services\CurrencyService;
use App\Services\TranslationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;

class JoinConversation extends Mailable
{
    use Queueable, SerializesModels;
    private $user, $token, $pwa;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user, $token , $pwa)
    {
        $this->user = $user;
        $this->token = $token;
        $this->pwa = $pwa;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {

        $inviterName = '';
        $subject = "";
        $profile = $this->user->profile;
        if($profile){
            $inviterName = $profile->profile_name;
        }


        $translationService = new TranslationService();
        $translations = $translationService->getKeyByLanguageCode(App::getLocale());
        if(array_key_exists('email_template_join_conversation_subject', $translations)){
            $subject = $translations['email_template_join_conversation_subject'];
        }

        if($this->pwa){
            $joinUrl = config("company.url.pwa").'/group-invitations/verify?token='.$this->token;
        }else{
            $joinUrl = config("company.url.client").'/group-invitations/verify?token='.$this->token;
        }

        $emailData = [
            'translations' => $translations,
            'inviterName' => $inviterName,
            'joinUrl' => $joinUrl,
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
        return $this->view('emails.joinConversationEmail')
            ->subject($subject)
            ->with($emailData);
    }
}
