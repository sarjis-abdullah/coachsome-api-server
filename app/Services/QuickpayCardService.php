<?php

namespace App\Services;

class QuickpayCardService
{

    private $client;

    public function __construct()
    {
        $this->client = (new QuickpayClientService())->getClient();
    }

    public function createQuickPayCard()
    {
        return $this->client->request->post('/cards')->asObject();
    }

    public function getQuickPayCardLink($cardID,$continueUrl, $cancelUrl)
    {
        $link = "";
        $response =  $this->client->request->put('/cards/' . $cardID . '/link', [
            "continueurl" => $continueUrl,
            "cancelurl" => $cancelUrl
        ])->asObject();
        if($response){
            $link = $response->url;
        }
        return $link;
    }

    public function getQuickPayCard($card_id)
    {
        $url = '/cards/' . $card_id;
        return $this->client->request->get($url);
    }

    public function cancelQuickPayCard($card_id)
    {
        $url = sprintf("/cards/%s/cancel", $card_id);
        return $this->client->request->post($url);
    }

    public function getCardToken($card_id)
    {
        $token = null;
        $response = $this->client->request->post(sprintf("/cards/%s/tokens", $card_id))->asArray();
        if(array_key_exists('token', $response)){
            $token = $response['token'];
        }
        return $token;
    }

}
