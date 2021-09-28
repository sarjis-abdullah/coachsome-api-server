<?php

use  Coachsome\ChatServer\Http\Controllers\UserController;

Route::group(['prefix' => 'api/chatServer'], function () {
    Route::post('users/{id}/offline', [UserController::class, 'doOffline']);
    Route::post('users/{id}/online', [UserController::class, 'doOnline']);
    Route::post('users/offline', [UserController::class, 'doOfflineAll']);
});

