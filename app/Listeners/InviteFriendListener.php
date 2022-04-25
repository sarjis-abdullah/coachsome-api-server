<?php

namespace App\Listeners;

use App\Mail\InviteFriendMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class InviteFriendListener
{
    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        Mail::to($event->inviteFriend['email'])->send(new InviteFriendMail($event->inviteFriend));
    }
}
