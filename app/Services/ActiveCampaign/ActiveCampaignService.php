<?php

namespace App\Services\ActiveCampaign;

use Illuminate\Support\Facades\Http;

class ActiveCampaignService
{
    const API_TOKEN = "6e9198c9264cd829b152badf665017ce5e613e1e3800102015ed65d9a6ed59f0bef82c03";
    const BASE_URL = "https://coachsome.api-us1.com/api/3";

    function post($endpoint, $payload)
    {
        return Http::withHeaders([
            'Api-Token' => self::API_TOKEN,
            'Accept' => 'application/json'
        ])->post(self::BASE_URL.$endpoint, $payload);
    }

    function createOrUpdateContact($payload)
    {
        return $this->post("/contact/sync", $payload);
    }

    function addTagToContact($payload)
    {
        return $this->post("/contactTags", $payload);
    }

    public function getCoachTagId()
    {
        return 21;
    }

    public function getAthleteTagId()
    {
        return 22;
    }
}
