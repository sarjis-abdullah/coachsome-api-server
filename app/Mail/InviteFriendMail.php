<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

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
         return $this->markdown('emails.inviteFriend')->subject("hello")->with([
             'inviteFriend' => $this->inviteFriend
         ]);
    }
}
