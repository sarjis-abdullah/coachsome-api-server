<?php

namespace App\Providers;

use App\Events\CreateNewContactUserEvent;
use App\Events\InviteFriendEvent;
use App\Events\SendEmailToContactUserEvent;
use App\Events\UserRegisteredEvent;
use App\Listeners\CreateNewContactUserListener;
use App\Listeners\InviteFriendListener;
use App\Listeners\SendEmailToContactUserListener;
use App\Listeners\UserInitialSetupListener;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        UserRegisteredEvent::class=>[
            UserInitialSetupListener::class
        ],
        InviteFriendEvent::class=>[
            InviteFriendListener::class
        ],
        CreateNewContactUserEvent::class=>[
            CreateNewContactUserListener::class
        ],
        SendEmailToContactUserEvent::class=>[
            SendEmailToContactUserListener::class
        ],
        \SocialiteProviders\Manager\SocialiteWasCalled::class => [
            // ... other providers
            \SocialiteProviders\Apple\AppleExtendSocialite::class.'@handle',
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
