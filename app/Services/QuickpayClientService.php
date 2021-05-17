<?php


namespace App\Services;


use QuickPay\QuickPay;

class QuickpayClientService
{
    private $apiKey;

    public function __construct()
    {
        $this->apiKey = env('QUICKPAY_API_KEY');
    }

    public function getClient()
    {
        return new QuickPay(":{$this->apiKey}");
    }

    public function getAccountId()
    {
        return env('QUICKPAY_ACC_ID');
    }
}
