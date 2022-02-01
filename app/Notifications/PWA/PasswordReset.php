<?php

namespace App\Notifications\PWA;

use App\Services\TranslationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\App;

class PasswordReset extends Notification
{
    use Queueable;

    /**
     * The password reset otp.
     *
     * @var string
     */
    public $otp;

    /**
     * The user model.
     *
     * @var string
     */
    public $user;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($otp, $user)
    {
        $this->otp = $otp;
        $this->user = $user;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Build the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $translations = [];
        $languageCode = App::getLocale();

        $translationService = new TranslationService();

        if ($languageCode == 'da') {
            $translations = $translationService->getKeyByLanguageCode($languageCode);
        } else {
            $translations = $translationService->getKeyByLanguageCode($languageCode);
        }

        $viewData = [
            "otp" => $this->otp,
            "translations" => $translations
        ];

        return (new MailMessage)->view("emails.PWA.passwordReset", $viewData);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
