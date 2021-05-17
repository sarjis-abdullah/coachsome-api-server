<?php
// Social Auth
Route::get('auth/login/{provider}', 'SocialAuthController@redirectToProvider');
Route::get('auth/login/{provider}/callback', 'SocialAuthController@handleProviderCallback');

// Email Template
Route::get('emails/pendingBookingRequest', function () {
    return view("emails.pendingBookingRequest");
});

Route::get('emails/baseReviewRequest', function () {
    return view("emails/baseReviewRequest");
});

Route::get('images/{size}/{filename}', function ($size, $filename) {
    return response()->file(storage_path('app/public/images/' . $size . '/' . $filename));
})->name("images");

