<?php

namespace App\Services\ActiveCampaign;

use Illuminate\Support\Facades\Http;

class ActiveCampaignService
{
    const API_TOKEN = "0aef8b26f5217504c0cad76bff3d2bf04b6876487ca71a9d19eed1b63601d0eed7137892";
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
