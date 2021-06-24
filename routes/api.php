<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\MarketplaceController;
use App\Http\Controllers\Api\V1\ContactController;
use App\Http\Controllers\Api\V1\MessageController;
use App\Http\Controllers\Api\V1\ChatController;
use App\Http\Controllers\Api\V1\General\HomeController;

/*
 * Public
 */
Route::group(['namespace' => '\App\Http\Controllers\Api\V1'], function () {
    // Booting
    Route::get('booting', 'BootingController@index')->name("booting");

    // Home page
    Route::get('pages/frontHome', [HomeController::class, 'index']);

    // Sport Categories
    Route::get('sportCategories', 'SportCategoryController@index');

    // Company Ratings
    Route::get('companyRatings', 'CompanyRatingController@index');


    // Profile
    Route::get('publicProfile/{userName}', 'ProfileController@getByUserName');

    // App Bar
    Route::get('appBar/front', 'AppBarController@getInitialData');

    // Pending Booking
    Route::post('pendingBookings', 'PendingBookingController@store');
    Route::post('pendingBookings/confirm', 'PendingBookingController@confirm');

    // Bookings
    Route::post('bookings', 'BookingController@index');

    // Translation
    Route::get('locale/translations', 'Admin\Translation\TranslationController@getTranslation');

    // Marketplaces
    Route::get('marketplaces', [MarketplaceController::class, 'index']);

    // Storage Video
    Route::get('storage/video/{name}', 'VideoController@getVideo');
});


/*
 * Authentication
 */
Route::group(['prefix' => 'auth', 'namespace' => '\App\Http\Controllers\Api\V1'], function () {
    // Client auth
    Route::post('login', 'Auth\LoginController@login');
    Route::post('register', 'Auth\RegisterController@register');
    Route::group(['middleware' => ['auth:api'],], function () {
        Route::post('logout', 'Auth\LoginController@logout');
    });

    // Admin auth
    Route::post('admin/login', 'Auth\AdminLoginController@login');
    Route::group(['middleware' => ['auth:api'],], function () {
        Route::post('admin/logout', 'Auth\AdminLoginController@logout');
    });

    // Verification
    Route::put('emailVerification', 'Auth\VerificationController@emailVerify');

    // Forgot password
    Route::post('recovery', 'Auth\ForgotPasswordController@sendResetLinkEmail');
    Route::post('password/reset', 'Auth\ResetPasswordController@reset');


    // Impersonate
    Route::group(['middleware' => ['sessions']], function () {
        Route::post('impersonate/{id}', 'Auth\ImpersonateController@impersonate')->middleware('auth:api');
        Route::get('impersonate/revert', 'Auth\ImpersonateController@revert')->middleware('auth:api');
    });

});

/*
 * General
 */
Route::group(['namespace' => '\App\Http\Controllers\Api\V1', 'middleware' => ['auth:api']], function () {
    // Payments
    Route::post('payments/quickpay/pay', 'QuickpayController@pay');
    Route::post('payments/quickpay/notify', 'QuickpayController@notify');

    // Chats
    Route::get('chats', [ChatController::class, 'index']);

    // Contacts
    Route::get('contacts', [ContactController::class, 'index']);

    // Messages
    Route::get('messages', [MessageController::class, 'index']);
    Route::post('messages', [MessageController::class, 'store']);
    Route::get('messages/newCount', [MessageController::class, 'getNewCount']);

    // Bookings
    Route::get('bookings/packages', 'BookingController@getBookingPackage');
    Route::post('bookings/acceptance', 'BookingController@changeStatus');


    // Booking Time
    Route::post('bookingTimes', 'BookingTimeController@store');
    Route::post('bookingTimes/acceptance', 'BookingTimeController@changeStatus');


    // Calenders
    Route::get('calenders/days', 'CalenderController@getTimeSlotByDateRange');

    // Reviews
    Route::resource('reviews', 'ReviewController');


    // Profile
    Route::get('profiles', 'ProfileController@index');
    Route::post('profiles', 'ProfileController@store');
    Route::post('profiles/images', 'ProfileController@uploadImage');

    Route::get('profileImages', 'Shared\ProfileController@getImage');
    Route::post('profileImages', 'Shared\ProfileController@uploadImage');
    Route::delete('profileImages', 'Shared\ProfileController@destroyImage');

    // Gallery
    Route::resource('images', 'ImageController');
    Route::resource('videos', 'VideoController');
    Route::resource('galleries', 'GalleryController');

    // Geography
    Route::resource('locations', 'LocationController');
    Route::resource('distances', 'DistanceController');
    Route::get('pages/geography', 'GeographyController@index');

    // Package
    Route::get('pages/package', 'PackageController@index');
    Route::post('pages/package/hourlyRate', 'PackageController@saveHourlyRate');
    Route::post('pages/package/quickBooking', 'PackageController@toggleQuickBooking');

    Route::resource('packages/default', 'DefaultPackageController');
    Route::resource('packages/camp', 'CampPackageController');

    Route::post('packages', 'PackageController@store');
    Route::put('packages/{id}', 'PackageController@update');
    Route::post('packages/remove/{id}', 'PackageController@destroy');
    Route::post('packages/changeStatus', 'PackageController@changeStatus');
    Route::post('packages/order', 'PackageController@updateOrder');

    // Availability
    Route::get('pages/availabilities', 'AvailabilityController@index');
    Route::post('availabilities/default', 'AvailabilityController@saveDefaultAvailability');
    Route::post('availabilities/update', 'AvailabilityController@updateAvailability');


    // Language
    Route::get('languages', 'LanguageController@index');

    // Category
    Route::get('sport/categories', 'SportCategoryController@index');

    // Tag
    Route::get('sport/tags', 'SportTagController@index');
    Route::post('sport/tags', 'SportTagController@store');

    // User
    Route::post('user/{userName}', 'UserController@updateUserName');
    Route::get('authUserInformation', 'UserController@getAuthUserInformation');

    // Setting
    Route::get('settings', 'SettingController@index');

    // Countries
    Route::get('countries', 'CountryController@index');

    // Drawer
    Route::get('drawer/back', 'DrawerController@getBackendDrawerInitialData');
    Route::post('drawer/back/changeActiveStatus', 'DrawerController@changeActiveStatus');
});

/*
 * Coach
 */
Route::group(['prefix' => 'coach', 'namespace' => '\App\Http\Controllers\Api\V1', 'middleware' => ['auth:api']], function () {
    Route::get('bookings', 'Coach\BookingController@index');
    Route::post('bookings/favourite', 'Coach\BookingController@changeFavourite');
    Route::get('bookingTimes', 'Coach\BookingTimeController@index');
    Route::get('progressStatus', 'Coach\ProgressController@index');

    Route::get('settings', 'Coach\SettingsController@index');
    Route::post('settings/changeEmail', 'Coach\SettingsController@changeEmail');
    Route::post('settings/changePassword', 'Coach\SettingsController@changePassword');
    Route::post('settings/update', 'Coach\SettingsController@update');

    Route::post('payout/request', 'Coach\PayoutRequestController@doRequest');

    // Timezones
    Route::get('timezones', 'Coach\TimezoneController@index');
    Route::get('balanceEarnings', 'Coach\BalanceEarningController@index');

    // Payout Information
    Route::get('payoutInformation', 'Coach\PayoutInformationController@index');
    Route::post('payoutInformation', 'Coach\PayoutInformationController@save');
});


/*
 * Athlete
 */
Route::group(['prefix' => 'athlete', 'namespace' => '\App\Http\Controllers\Api\V1', 'middleware' => ['auth:api'],], function () {
    Route::get('profiles', 'Athlete\ProfileController@index');
    Route::post('profiles', 'Athlete\ProfileController@store');
    Route::post('profiles/images', 'Athlete\ProfileController@uploadImage');
    Route::get('bookings', 'Athlete\BookingController@index');
    Route::post('bookings/favourite', 'Athlete\BookingController@changeFavourite');
    Route::get('bookingTimes', 'Athlete\BookingTimeController@index');
    Route::get('searchValues', 'Athlete\SearchValueController@index');
    Route::get('explore/coach', 'Athlete\PackageController@explore');
});


/*
 * Admin
 */
Route::group(['prefix' => 'admin', 'namespace' => '\App\Http\Controllers\Api\V1', 'middleware' => ['auth:api', 'isAdmin']], function () {
    Route::apiResource('users', 'Admin\User\UserController');
    Route::apiResource('userLogs', 'Admin\User\UserLogController');

    Route::get('payout/requests', 'Admin\Payout\PayoutRequestController@index');
    Route::post('payout/requests/paid', 'Admin\Payout\PayoutRequestController@paid');

    Route::get('pendingCustomers', 'Admin\Customer\PendingCustomerController@index');

    // Order list
    Route::get('orderList', 'Admin\Order\OrderListController@index');

    // Dashboard
    Route::get('dashboard', 'Admin\Dashboard\DashboardController@index');

    // Translations
    Route::get('translations', 'Admin\Translation\TranslationController@index');
    Route::post('translations', 'Admin\Translation\TranslationController@store');
    Route::put('translations', 'Admin\Translation\TranslationController@update');

    // Seo Translations
    Route::get('translations/seo', 'Admin\Translation\SeoTranslationController@index');
    Route::post('translations/seo', 'Admin\Translation\SeoTranslationController@save');
});
