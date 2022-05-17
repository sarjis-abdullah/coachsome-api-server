<?php

namespace App\Mail;

use App\Services\TranslationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

class SendEmailToContactUserMail extends Mailable
{
    use Queueable, SerializesModels;

    private $contactUser;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($contactUser)
    {
        $this->contactUser = $contactUser;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $coachName = Auth::user()->first_name." ".Auth::user()->last_name;
        $athleteName = $this->contactUser['firstName']." ".$this->contactUser['lastName'];
        $translationService = new TranslationService();
        $translations = $translationService->getKeyByLanguageCode(App::getLocale());
        $subject = "Invitation from ". $coachName;
        return $this->markdown('emails.SendEmailToContactUser')->subject($subject)->with([
            'contactUser' => $this->contactUser,
            'coachName' => $athleteName,
            'athleteName' => $athleteName,
            'translations' => $translations,
        ]);
    }
}
