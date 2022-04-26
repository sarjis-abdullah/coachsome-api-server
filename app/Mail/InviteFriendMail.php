<?php

namespace App\Mail;

use App\Services\TranslationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

class InviteFriendMail extends Mailable
{
    use Queueable, SerializesModels;

    public $inviteFriend;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($inviteFriend)
    {
        $this->inviteFriend = $inviteFriend;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $translationService = new TranslationService();
        $translations = $translationService->getKeyByLanguageCode(App::getLocale());
        $subject = "Invite Friends to Join Coachsome";
         return $this->markdown('emails.inviteFriend')->subject($subject)->with([
             'inviteFriend' => $this->inviteFriend,
             'translations' => $translations,
         ]);
    }
}
