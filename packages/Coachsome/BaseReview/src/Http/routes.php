<?php

use Coachsome\BaseReview\Http\Controllers\BaseReviewController;

Route::group(['prefix' => 'api/baseReviews'], function () {
    Route::get('/all', [BaseReviewController::class, 'getAll']);
    Route::group(['middleware' => ['auth:api']],function(){
        Route::get('/', [BaseReviewController::class, 'index']);
        Route::post('/', [BaseReviewController::class, 'store']);
        Route::get('/profileInfo', [BaseReviewController::class, 'getProfileInformation']);
        Route::post('/request', [BaseReviewController::class, 'makeRequest']);
    });
});
