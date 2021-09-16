<?php

return [
    /*
    |--------------------------------------------------------------------------
    |  Company Information
    |--------------------------------------------------------------------------
    |
    | This configuration define company related information
    |
    */
    'url' => [
        'terms_page' => env('APP_CLIENT_DOMAIN_TERMS_PAGE'),
        'order_list_page' => env('APP_CLIENT_DOMAIN') . '/admin/orders',
        'home_page' => env('APP_CLIENT_DOMAIN'),
        'linkedin' => "https://www.linkedin.com/company/coachsome/",
        'facebook' => "https://www.facebook.com/coachsome/app/212104595551052/?ref=page_internal",
        'linkedin_icon' => env('APP_SERVER_DOMAIN_PUBLIC_PATH') . '/assets/images/icons/linkedin.png',
        'facebook_icon' => env('APP_SERVER_DOMAIN_PUBLIC_PATH') . '/assets/images/icons/facebook.png',
        'logo_icon' => env('APP_SERVER_DOMAIN_PUBLIC_PATH') . '/assets/images/icons/logo.png',
        'logo' => env('APP_SERVER_DOMAIN_PUBLIC_PATH') . '/assets/images/logos/logo.png',
    ]
];