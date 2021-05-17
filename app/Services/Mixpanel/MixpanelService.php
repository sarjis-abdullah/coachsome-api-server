<?php


namespace App\Services\Mixpanel;


class MixpanelService
{
    private $instance = null;

    public function init()
    {
        $this->instance = \Mixpanel::getInstance("2592dbd603679d46a6f5e3a7aa346492");
        return $this;
    }

    public function track($eventName, $properties = [])
    {
        $this->instance->track($eventName, $properties);
    }

    public function peopleSet($user)
    {
        $userType = "";
        $roles = $user->roles;
        if($roles->count()){
            $userType = $roles->first()->display_name;
        }
        $this->instance->people->set($user->id, array(
            '$first_name' => $user->first_name,
            '$last_name' => $user->last_name,
            '$email' => $user->email,
            'type'=> $userType
        ), $ip = 0, $ignore_time = true);
        return $this;
    }
}
