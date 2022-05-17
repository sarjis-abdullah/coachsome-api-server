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
        $isAthlete = Auth::user()->hasRole([Constants::ROLE_KEY_ATHLETE, Constants::ROLE_KEY_SUPER_ADMIN]);
        if ($isAthlete) {
            $hasItem = ContactUser::where('email', '=', $event->contactUserRequest['email'])->first();
            if (!$hasItem){
                $event->contactUserRequest['token'] = time().'-'.mt_rand();
                ContactUser::create($event->contactUserRequest);
            }
        }
    }
}
