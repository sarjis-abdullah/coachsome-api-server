<?php

namespace App\Listeners;

use App\Data\Constants;
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
        $hasRole = Auth::user()->hasRole([Constants::ROLE_KEY_ATHLETE, Constants::ROLE_KEY_SUPER_ADMIN]);
        if ($hasRole) {
            $hasItem = ContactUser::where('email', '=', $event->contactUserRequest['email'])->first();
            if (!$hasItem)
                ContactUser::create($event->contactUserRequest);
        }
    }
}
