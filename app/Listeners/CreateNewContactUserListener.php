<?php

namespace App\Listeners;

use App\Entities\ContactUser;
use Illuminate\Support\Facades\Auth;

class CreateNewContactUserListener
{


    /**
     * Handle the event.
     *
     * @param object $event
     */
    public function handle(object $event)
    {
        $hasItem = ContactUser::where('email', '=', $event->contactUserRequest['email'])
            ->where('receiverUserId', '=', Auth::user()->id)
            ->first();
        if (!$hasItem){
            $event->contactUserRequest['token'] = time().'-'.mt_rand();
            ContactUser::create($event->contactUserRequest);
        }
    }
}
