<?php

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
