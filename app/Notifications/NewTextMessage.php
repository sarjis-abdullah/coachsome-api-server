<?php

namespace App\Notifications;

use App\Entities\Booking;
use App\Entities\BookingLocation;
use App\Entities\User;
use App\Services\TranslationService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\App;

class NewTextMessage extends Notification
{

    private $message;
    private $translationService;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($message)
    {
        $this->message = $message;
        $this->translationService = new TranslationService();
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
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $translations = null;

        $senderUser = User::find($this->message->sender_user_id);
        $receiverUser = User::find($this->message->receiver_user_id);
        $translations = $this->translationService->getKeyByLanguageCode(App::getLocale());

        $messageTime = Carbon::parse($this->message->date_time_iso)->toDayDateTimeString();
        $textContent = $this->message->text_content;

        $emailData = [
            'translations' => $translations,
            'messageTime' => $messageTime,
            'textContent' => $textContent,
            'chatNowUrl' => env('APP_CLIENT_DOMAIN_CHAT_PAGE') . '?userId=' . $senderUser->id,
            'senderUserName' => $senderUser->first_name . ' ' . $senderUser->last_name,
            'receiverUserName' => $receiverUser->first_name . ' ' . $receiverUser->last_name,
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

        return (new MailMessage)->view('emails.newTextMessage', $emailData);

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
        ];
    }
}
