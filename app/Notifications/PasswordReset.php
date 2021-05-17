<?php

namespace App\Notifications;

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
     * The password reset token.
     *
     * @var string
     */
    public $token;

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
    public function __construct($token, $user)
    {
        $this->token = $token;
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
            "resetUrl" => env('APP_CLIENT_DOMAIN_PASSWORD_RESET_URL') . '?email=' . $this->user->email . '&token=' . $this->token,
            "translations" => $translations
        ];

        return (new MailMessage)->view("emails.passwordReset", $viewData);
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
