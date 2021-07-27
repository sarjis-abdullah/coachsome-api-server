<?php

use Illuminate\Support\Facades\Route;


Route::group(['namespace' => '\App\Http\Controllers\Api\V1'], function () {
    /*
    * General
    */
    Route::group(['namespace' => 'General'], function () {
        // Booting
        Route::get('booting', 'BootingController@index')->name("booting");

        // Home page
        Route::get('pages/frontHome', "HomeController@index");

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
        Route::get('translations', "TranslationController@index");

        // Marketplaces
        Route::get('marketplaces', "MarketplaceController@index");

        // Storage Video
        Route::get('storage/video/{name}', 'VideoController@getVideo');

        Route::group(['middleware' => ['auth:api']], function () {
            // Payments
            Route::post('payments/quickpay/pay', 'QuickpayController@pay');
            Route::post('payments/quickpay/notify', 'QuickpayController@notify');

            // Chats
            Route::get('chats', "ChatController@index");

            // Contacts
            Route::get('contacts', "ContactController@index");

            // Messages
            Route::get('messages', "MessageController@index");
            Route::post('messages', "MessageController@store");
            Route::get('messages/newCount', "MessageController@getNewCount");

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

            // Profile Images
            Route::get('profileImages', "GeneralProfileController@getImage");
            Route::post('profileImages', "GeneralProfileController@uploadImage");
            Route::delete('profileImages', "GeneralProfileController@destroyImage");

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
    });

    /*
    * Coach
    */
    Route::group(['prefix' => 'coach', 'namespace' => 'Coach', 'middleware' => ['auth:api']], function () {
        Route::get('bookings', 'BookingController@index');
        Route::post('bookings/favourite', 'BookingController@changeFavourite');
        Route::get('bookingTimes', 'BookingTimeController@index');
        Route::get('progressStatus', 'ProgressController@index');

        Route::get('settings', 'SettingsController@index');
        Route::post('settings/changeEmail', 'SettingsController@changeEmail');
        Route::post('settings/changePassword', 'SettingsController@changePassword');
        Route::post('settings/update', 'SettingsController@update');

        Route::post('payout/request', 'PayoutRequestController@doRequest');

        // Timezones
        Route::get('timezones', 'TimezoneController@index');
        Route::get('balanceEarnings', 'BalanceEarningController@index');

        // Payout Information
        Route::get('payoutInformation', 'PayoutInformationController@index');
        Route::post('payoutInformation', 'PayoutInformationController@save');
    });

    /*
    * Athlete
    */
    Route::group(['prefix' => 'athlete', 'namespace' => 'Athlete', 'middleware' => ['auth:api'],], function () {
        Route::get('profiles', 'ProfileController@index');
        Route::post('profiles', 'ProfileController@store');
        Route::post('profiles/images', 'ProfileController@uploadImage');
        Route::get('bookings', 'BookingController@index');
        Route::post('bookings/favourite', 'BookingController@changeFavourite');
        Route::get('bookingTimes', 'BookingTimeController@index');
        Route::get('searchValues', 'SearchValueController@index');
        Route::get('explore/coach', 'PackageController@explore');
    });


    /*
     * Admin
     */
    Route::group(['prefix' => 'admin', 'namespace' => 'Admin', 'middleware' => ['auth:api', 'isAdmin']], function () {
        Route::apiResource('users', 'User\UserController');
        Route::apiResource('userLogs', 'User\UserLogController');

        Route::get('payout/requests', 'Payout\PayoutRequestController@index');
        Route::post('payout/requests/paid', 'Payout\PayoutRequestController@paid');

        Route::get('pendingCustomers', 'Customer\PendingCustomerController@index');

        // Order list
        Route::get('orderList', 'Order\OrderListController@index');

        // Dashboard
        Route::get('dashboard', 'Dashboard\DashboardController@index');

        // Translations
        Route::get('translations', 'Translation\TranslationController@index');
        Route::post('translations', 'Translation\TranslationController@store');
        Route::put('translations', 'Translation\TranslationController@update');

        // Seo Translations
        Route::get('translations/seo', 'Translation\SeoTranslationController@index');
        Route::post('translations/seo', 'Translation\SeoTranslationController@save');
    });


    /*
     * Authentication
     */
    Route::group(['prefix' => 'auth', 'namespace' => 'Auth'], function () {
        // Client auth
        Route::post('login', 'LoginController@login');
        Route::post('register', 'RegisterController@register');
        Route::group(['middleware' => ['auth:api'],], function () {
            Route::post('logout', 'LoginController@logout');
        });

        // Admin auth
        Route::post('admin/login', 'AdminLoginController@login');
        Route::group(['middleware' => ['auth:api'],], function () {
            Route::post('admin/logout', 'AdminLoginController@logout');
        });

        // Verification
        Route::put('emailVerification', 'VerificationController@emailVerify');

        // Forgot password
        Route::post('recovery', 'ForgotPasswordController@sendResetLinkEmail');
        Route::post('password/reset', 'ResetPasswordController@reset');


        // Impersonate
        Route::group(['middleware' => ['sessions']], function () {
            Route::post('impersonate/{id}', 'ImpersonateController@impersonate')->middleware('auth:api');
            Route::get('impersonate/revert', 'ImpersonateController@revert')->middleware('auth:api');
        });

    });
});




