<?php


namespace App\Services;


class PaypalService
{

    /**
     * Returns PayPal HTTP client instance with environment that has access
     * credentials context. Use this instance to invoke PayPal APIs, provided the
     * credentials have access.
     */
    public static function client()
    {
        return new PayPalHttpClient(self::environment());
    }

    /**
     * Set up and return PayPal PHP SDK environment with PayPal access credentials.
     * This sample uses SandboxEnvironment. In production, use LiveEnvironment.
     */
    public static function environment()
    {
        $clientId = env("PAYPAL_CLIENT_ID");
        $clientSecret = env("PAYPAL_CLIENT_SECRET");
        return new SandboxEnvironment($clientId, $clientSecret);
    }
}
