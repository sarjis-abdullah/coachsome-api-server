<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserRegisteredEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user = null;
    public $userType = '';
    public $provider = false;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($user, $userType, $provider = false)
    {
        $this->user = $user;
        $this->userType = $userType;
        $this->provider = $provider;
    }

}
