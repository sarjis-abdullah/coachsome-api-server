<?php

// Social Auth
Route::get('auth/login/{provider}', 'SocialAuthController@redirectToProvider');
Route::get('auth/login/{provider}/callback', 'SocialAuthController@handleProviderCallback');

// Email Template
Route::get('emails/newTextMessage', function () {
    return view("emails.newTextMessage");
});

Route::get('emails/baseReviewRequest', function () {
    return view("emails/baseReviewRequest");
});

Route::get('images/{size}/{filename}', function ($size, $filename) {
    return response()->file(storage_path('app/public/images/' . $size . '/' . $filename));
})->name("images");

Route::get('images/{filename}', function ($filename) {
    return response()->file(storage_path('app/public/images/' . $filename));
})->name("images.gallery");


