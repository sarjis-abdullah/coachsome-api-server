<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PendingBookingRequestConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public $fullName;
    public $link;
    public $translation;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(String $fullName, String $link, array $translation)
    {
        $this->fullName = $fullName;
        $this->link = $link;
        $this->translation = $translation;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = array_key_exists('pending_booking_request_mail_subject',$this->translation)
            ? $this->translation['pending_booking_request_mail_subject']
            : 'Thank you for signing up to Coachsome!';

        return $this
            ->subject($subject)
            ->view('emails.pendingBookingRequestConfirmation')
            ->with([
                'fullName'=> $this->fullName,
                'link'=>$this->link,
                'translation'=> $this->translation
            ]);
    }
}
