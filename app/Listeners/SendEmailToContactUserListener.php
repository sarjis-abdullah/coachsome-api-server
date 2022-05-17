<?php

namespace App\Listeners;

use App\Mail\SendEmailToContactUserMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendEmailToContactUserListener
{
    /**
     * Handle the event.
     *
     * @param object $event
     * @return void
     */
    public function handle(object $event)
    {
        Mail::to($event->contactUser['email'])->send(new SendEmailToContactUserMail($event->contactUser));
    }
}
