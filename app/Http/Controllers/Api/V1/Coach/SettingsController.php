<?php

namespace App\Http\Controllers\Api\V1\Coach;

use App\Data\StatusCode;
use App\Entities\NotificationCategory;
use App\Entities\User;
use App\Entities\UserSetting;
use App\Http\Controllers\Controller;
use App\Services\Locale\LocaleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use PeterColes\Countries\CountriesFacade;
use PragmaRX\Countries\Package\Countries;

class SettingsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            $localeService = new LocaleService();

            $locale = $localeService->currentLocale();


            $authUser = Auth::user();

            if(!$authUser){
                throw new \Exception('Sorry! user not found.');
            }

            $settings = null;
            $userSettings = [];
            $allCountryList = [];

            $settings = $authUser->settings;
            if(!$settings) {
                $settings = new UserSetting();
                $settings->user_id = $authUser->id;
                $settings->first_name = $authUser->first_name;
                $settings->last_name = $authUser->last_name;
                $settings->save();
            }

            $notificationCategoryList = NotificationCategory::get();

            $countryList = $localeService->countryList($locale);
            foreach ($countryList as $key=>$item) {
                $newCountry = new \stdClass();
                $newCountry->code = $key;
                $newCountry->displayName = $item;
                $allCountryList[] = $newCountry;
            }

            if($settings){
                $userSettings['firstName'] = $authUser->first_name;
                $userSettings['lastName'] = $authUser->last_name;
                $userSettings['email'] = $authUser->email;
                $userSettings['country'] = $settings->cca2;
                $userSettings['address'] = $settings->address;
                $userSettings['zipCode'] = $settings->zip;
                $userSettings['city'] = $settings->city;
                $userSettings['timezone'] = $settings->timezone;
                $userSettings['has_password'] = $authUser->has_password;


                $activeNotificationCategories = !empty($settings->notification_category)
                    ? NotificationCategory::whereIn('id', json_decode($settings->notification_category,true))->get()
                    : [] ;

                $notificationCategoryList->each(function($item) use($activeNotificationCategories){
                    foreach ($activeNotificationCategories as $activeItem) {
                        if($item->id == $activeItem->id){
                            $item->status = true;
                            break;
                        }
                    }

                });

                $userSettings['activeNotificationCategories'] = $activeNotificationCategories;
            }

            return response()->json([
                'countryList'=> $allCountryList,
                'notificationCategoryList'=> $notificationCategoryList,
                'userSetting' => $userSettings
            ], StatusCode::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'message'=>$e->getLine().$e->getMessage()
            ], StatusCode::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function update(Request $request)
    {
        $firstName = $request->firstName;
        $lastName =  $request->lastName;
        $cca2 = $request->cca2;
        $zipCode = $request->zipCode;
        $lat = $request->lat;
        $long = $request->long;
        $city = $request->city;
        $address = $request->address;
        $timezone = $request->timezone;
        $notificatonCategories = $request->notificatonCategories;

        $authUser = Auth::user();
        $setting = $authUser ? $authUser->settings : null;

        try {
            if(!$setting){
                throw new \Exception('Setting not found');
            }

            $setting->first_name = $firstName;
            $setting->last_name = $lastName;
            $setting->cca2 = $cca2;
            $setting->zip = $zipCode;
            $setting->lat = $lat;
            $setting->long = $long;
            $setting->city = $city;
            $setting->address = $address;
            $setting->timezone = $timezone;
            $setting->notification_category = json_encode($notificatonCategories);
            $setting->save();

            User::where('email', $authUser->email)->update([
                'first_name' => $firstName,
                'last_name' => $lastName,
            ]);

            return response()->json([
                'message'=> __('settings.success_update'),
            ], StatusCode::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'message'=>$e->getLine().$e->getMessage()
            ], StatusCode::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function changeEmail(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'email' => "required|email|unique:users,email",
                'password' => "required",
            ]);

            if ($validator->fails()) {
               throw new \Exception($validator->getMessageBag()->first());
            }

            $email = $request->email;
            $password = $request->password;

            $authUser = Auth::user();

            $emailChangingUser = User::where('email', $authUser->email)
                ->where('password',  Hash::make($password))
                ->first();

            if(!$emailChangingUser){
                throw new \Exception('Sorry! password was incorrect.');
            }

            $authUser->email = $email;
            $authUser->save();

            return response()->json([
                'message'=>'Successfully changed your email.'
            ], StatusCode::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'message'=>$e->getMessage()
            ], StatusCode::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function changePassword(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'oldPassword' => "required",
                'newPassword' => "required",
            ]);

            if ($validator->fails()) {
                throw new \Exception($validator->getMessageBag()->first());
            }

            $oldPassword = $request->oldPassword;
            $newPassword = $request->newPassword;

            $authUser = Auth::user();

            $passwordChangedUser = User::where('email', $authUser->email)
                ->where('password',  Hash::make($oldPassword))
                ->first();

            if(!$passwordChangedUser){
                throw new \Exception('Sorry! old password was not correct.');
            }

            $authUser->password =  Hash::make($newPassword);
            $authUser->save();

            return response()->json([
                'message'=>'Successfully changed your password.'
            ], StatusCode::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'message'=>$e->getMessage()
            ], StatusCode::HTTP_UNPROCESSABLE_ENTITY);
        }
    }



}
