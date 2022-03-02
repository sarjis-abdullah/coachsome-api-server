<?php

use App\Utils\CurrencyUtil;
use Illuminate\Support\Facades\Route;

// Social Auth
Route::get('auth/login/{provider}', 'SocialAuthController@redirectToProvider');
Route::get('auth/login/{provider}/callback', 'SocialAuthController@handleProviderCallback');


// Gift card payment
Route::get('gift-cards/payments/continue', 'GiftCardPaymentCallbackController@continue')->name('gift-cards.payments.continue');
Route::get('gift-cards/payments/cancel', 'GiftCardPaymentCallbackController@cancel')->name('gift-cards.payments.cancel');
Route::get('gift-cards/template', function () {
    return view("emails.giftCard");
});


// Email Template
Route::get('emails/newOrderCapture', function () {
    return view("emails.newOrderCapture");
});

Route::get('emails/baseReviewRequest', function () {
    return view("emails/baseReviewRequest");
});

Route::get('emails/join-conversation-email', function () {
    return view("emails/joinConversationEmail");
});

Route::get('images/{size}/{filename}', function ($size, $filename) {
    return response()->file(storage_path('app/public/images/' . $size . '/' . $filename));
})->name("images");

Route::get('images/{filename}', function ($filename) {
    return response()->file(storage_path('app/public/images/' . $filename));
})->name("images.gallery");


// Currency conversion
Route::get('/currency/{amount}/{from}/{to}/latest', function ($amount, $from, $to) {
    return CurrencyUtil::convert($amount, $from, $to);
});

Route::get('/currency/{amount}/{from}/{to}/{date}', function ($amount, $from, $to, $date) {
    return CurrencyUtil::convert($amount, $from, $to, $date);
});

// Swagger
Route::get('/', function () {
    return view('swagger.index');
});

Route::get('/docs/api-docs.yml', function () {
    return file_get_contents(base_path("resources/views/swagger/openapi.yaml"));
})->name('openapi');

Route::get('/swagger-api', function () {
    return file_get_contents(base_path("resources/views/swagger/openapi.yaml"));
});

Route::get('/swagger/paths/{tag}/{filename}', function ($tag, $filename) {
    return file_get_contents(base_path("resources/views/swagger/paths/${tag}/${filename}.yaml"));
});
