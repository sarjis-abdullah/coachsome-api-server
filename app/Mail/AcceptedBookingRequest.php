<?php

namespace App\Mail;

use App\Entities\Booking;
use App\Entities\BookingLocation;
use App\Entities\User;
use App\Services\TranslationService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;

class AcceptedBookingRequest extends Mailable
{
    use Queueable, SerializesModels;

    private $bookingTime = null;
    private $translationService;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($bookingTime)
    {
        $this->bookingTime = $bookingTime;
        $this->translationService = new TranslationService();
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $packageName = '';
        $translations = null;

        $senderUser = User::find($this->bookingTime->requester_user_id);
        $receiverUser = User::find($this->bookingTime->requester_to_user_id);
        $booking = Booking::find($this->bookingTime->booking_id);

        $order = $booking->order;
        $packageSnapshot = $order ? json_decode($order->package_snapshot) : null;
        $translations = $this->translationService->getKeyByLanguageCode(App::getLocale());
        $bookingTimeLocation = BookingLocation::where('booking_time_id', $this->bookingTime->id)->first();

        $date = date('d-m-Y', strtotime($this->bookingTime->calender_date));
        $time = $this->bookingTime->start_time . ' - ' . $this->bookingTime->end_time;
        $location = $bookingTimeLocation ? $bookingTimeLocation->address : '';

        if ($packageSnapshot) {
            $packageName = $packageSnapshot->details ? $packageSnapshot->details->title : '';
        }

        $emailData = [
            'packageName' => $packageName,
            'translations' => $translations,
            'date' => $date,
            'time' => $time,
            'location' => $location,
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
        return $this->view('emails.acceptedBookingRequest')
            ->with($emailData);
    }
}
