<?php

use  Coachsome\ChatServer\Http\Controllers\UserController;
use  Coachsome\ChatServer\Http\Controllers\GroupController;

Route::group(['prefix' => 'api/chat-server'], function () {
    Route::post('users/{id}/offline', [UserController::class, 'doOffline']);
    Route::post('users/{id}/online', [UserController::class, 'doOnline']);
    Route::post('users/offline', [UserController::class, 'doOfflineAll']);
    Route::post('groups/{id}/users', [GroupController::class, 'getConnectedUser']);
});

