<?php

use Illuminate\Support\Facades\Route;


Route::group(['namespace' => '\App\Http\Controllers\Api\V1'], function () {
    /*
    * General
    */
    Route::group(['namespace' => 'General'], function () {
        // Booting
        Route::get('booting', 'BootingController@index')->name("booting");

        // Search
        Route::get('marketplace-searches', 'SearchController@index');

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

        // Translation
        Route::get('translations', "TranslationController@index");

        // Marketplaces
        Route::get('marketplaces', "MarketplaceController@index");

        // Storage Video
        Route::get('storage/video/{name}', 'VideoController@getVideo');

        // Invite-friends
        Route::get('accept-friend-invitation', 'InviteFriendController@acceptFriendInvitation');

        Route::group(['middleware' => ['auth:api']], function () {

            // bookings
            Route::get('bookings', 'BookingController@index');

            // payments
            Route::post('payments/quickpay/pay', 'QuickpayController@pay');
            Route::post('payments/quickpay/notify', 'QuickpayController@notify');

            // chats
            Route::get('chats', "ChatController@index");

            // chat settings
            Route::get('chatSettings', "ChatSettingController@index");
            Route::post('chatSettings/enterPress', "ChatSettingController@enterPress");

            // groups
            Route::apiResource('groups', "GroupController");
            Route::put('groups/{id}/change-topic', "GroupController@changeTopic");
            Route::post('groups/{id}/save-image', "GroupController@saveImage");
            Route::apiResource('group-messages', "GroupMessageController");
            Route::post('group-messages/attachment', "GroupMessageController@storeAttachment");
            Route::post('group-invitations/groups/{id}', "GroupInvitationController@invite");
            Route::post('group-invitations/verify', "GroupInvitationController@verify");
            Route::get('group-invitations/private-users', "GroupInvitationController@getPrivateUser");

            // contacts
            Route::get('contacts', "ContactController@index");
            Route::post('contacts/resetNewMessageInfo', "ContactController@resetContactNewMessageInformation");
            Route::post('contacts/archive', "ContactController@archive");
            Route::post('contacts/unarchive', "ContactController@unarchive");
            Route::post('contacts/unread', "ContactController@unread");
            Route::get('contacts/private-users', "ContactController@getPrivateUser");

            // messages
            Route::get('messages', "MessageController@index");
            Route::post('messages', "MessageController@store");
            Route::post('messages/attachment', "MessageController@storeAttachment");
            Route::get('messages/newCount', "MessageController@getNewCount");

            // bookings
            Route::get('bookings/packages', 'BookingController@getBookingPackage');
            Route::post('bookings/acceptance', 'BookingController@changeStatus');


            // booking times
            Route::post('bookingTimes', 'BookingTimeController@store');
            Route::post('bookingTimes/acceptance', 'BookingTimeController@changeStatus');

            // calenders
            Route::get('calenders/days', 'CalenderController@getTimeSlotByDateRange');

            // reviews
            Route::resource('reviews', 'ReviewController');


            // profiles
            Route::get('profiles', 'ProfileController@index');
            Route::post('profiles', 'ProfileController@store');
            Route::post('profiles/images', 'ProfileController@uploadImage');

            // profile images
            Route::get('profileImages', "GeneralProfileController@getImage");
            Route::post('profileImages', "GeneralProfileController@uploadImage");
            Route::delete('profileImages', "GeneralProfileController@destroyImage");

            // Gallery
            Route::resource('images', 'ImageController');
            Route::resource('videos', 'VideoController');
            Route::resource('galleries', 'GalleryController');

            // geography
            Route::resource('locations', 'LocationController');
            Route::resource('distances', 'DistanceController');
            Route::get('pages/geography', 'GeographyController@index');

            // package
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

            // availabilities
            Route::get('pages/availabilities', 'AvailabilityController@index');
            Route::post('availabilities/default', 'AvailabilityController@saveDefaultAvailability');
            Route::post('availabilities/update', 'AvailabilityController@updateAvailability');


            // languages
            Route::get('languages', 'LanguageController@index');

            // categories
            Route::get('sport/categories', 'SportCategoryController@index');

            // tags
            Route::get('sport/tags', 'SportTagController@index');
            Route::post('sport/tags', 'SportTagController@store');

            // user
            Route::post('user/{userName}', 'UserController@updateUserName');
            Route::get('users/{id}', 'UserController@show');
            Route::get('authUserInformation', 'UserController@getAuthUserInformation');

            // setting
            Route::get('settings', 'SettingController@index');

            // countries
            Route::get('countries', 'CountryController@index');

            // drawer
            Route::get('drawer/back', 'DrawerController@getBackendDrawerInitialData');
            Route::post('drawer/back/changeActiveStatus', 'DrawerController@changeActiveStatus');

            // accounts
            Route::delete('accounts/delete', 'AccountController@delete');

            // securities
            Route::get('securities', 'SecuritySettingController@index');

            // verifications
            Route::post('verifications/email-verify', 'VerificationController@verifyEmail');
            Route::post('verifications/phone-verify', 'VerificationController@verifyPhone');
            Route::post('verifications/facbook-verify', 'VerificationController@verifyFacebook');
            Route::post('verifications/google-verify', 'VerificationController@verifyGoogle');
            Route::post('verifications/twitter-verify', 'VerificationController@verifyTwitter');

            // Invite-friends
            Route::post('invite-friend', 'InviteFriendController@inviteFriends');
            Route::get('invite-friend', 'InviteFriendController@index');

            Route::group(['namespace' => 'Gift'], function () {
                // gift-cards
                Route::post('gift-cards/pay', 'GiftCardController@pay');
                Route::get('gift-cards/orders/{id}', 'GiftCardController@getOrder');
                Route::get('gift-cards/{id}/download', 'GiftCardController@downloadGiftCard');

                // gift-balances
                Route::get('gift-balances', 'GiftBalanceController@index');

                // gift-transactions
                Route::get('gift-transactions', 'GiftTransactionController@index');
                Route::post('gift-transactions', 'GiftTransactionController@store');
            });

            //payment-cards
            Route::get("payment-cards", "PaymentCardController@index");
            Route::post("payment-cards", "PaymentCardController@store");
            Route::post("payment-cards/cancel", "PaymentCardController@cancel");
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

        // timezones
        Route::get('timezones', 'TimezoneController@index');
        Route::get('balanceEarnings', 'BalanceEarningController@index');

        // payout information
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

        // settings
        Route::put('settings/reset-email', 'SettingController@resetEmail');
        Route::put('settings/reset-password', 'SettingController@resetEmail');
        Route::get('settings', 'SettingController@index');
        Route::put('settings/{id}', 'SettingController@update');
    });


    /*
     * Admin
     */
    Route::group(['prefix' => 'admin', 'namespace' => 'Admin', 'middleware' => ['auth:api', 'isAdmin']], function () {
        Route::apiResource('users', 'User\UserController');
        Route::apiResource('userLogs', 'User\UserLogController');
        Route::apiResource('promoCodes', 'PromoCode\PromoCodeController');

        Route::get('payout/requests', 'Payout\PayoutRequestController@index');
        Route::post('payout/requests/paid', 'Payout\PayoutRequestController@paid');

        Route::get('pendingCustomers', 'Customer\PendingCustomerController@index');

        // Order list
        Route::get('orderList', 'Order\OrderListController@index');

        // Tracking Codes
        Route::get('tracking-codes/{code}', 'PromoCode\TrackingCodeController@index');

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

     /*
     * PWA
     */
    Route::group(['prefix' => 'pwa', 'namespace' => 'PWA'], function () {
        // Client auth
        Route::group(['namespace' => 'Auth'], function () {
            Route::post('otp-validation', 'RegisterController@otpValidation');
            Route::post('register', 'RegisterController@register');
            Route::post('post-register', 'RegisterController@postRegister');
            Route::post('attach-user-role', 'RegisterController@attachUserRole');
            // Forgot password
            Route::post('recovery', 'ForgotPasswordController@sendResetOTP');
            Route::post('password/otp-validation', 'ForgotPasswordController@otpValidation');
            Route::post('password/reset-request', 'ResetPasswordController@otpExist');
            Route::post('password/reset', 'ResetPasswordController@reset');
        });
    });
});
