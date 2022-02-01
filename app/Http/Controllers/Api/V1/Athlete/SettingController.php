<?php

namespace App\Http\Controllers\Api\V1\Athlete;

use App\Data\SettingValue;
use App\Data\StatusCode;
use App\Entities\NotificationSetting;
use App\Entities\SocialAccount;
use App\Entities\User;
use App\Http\Controllers\Controller;
use App\Http\Resources\Setting\NotificationSettingResource;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class SettingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $authUser = Auth::user();
            $settings = NotificationSetting::where('user_id', $authUser->id)->first();
            if (!$settings) {
                $settings = NotificationSetting::create([
                    'user_id' => Auth::id(),
                    'inbox_message' => SettingValue::ID_EMAIL,
                    'order_message' => SettingValue::ID_EMAIL,
                    'order_update' => SettingValue::ID_EMAIL,
                    'booking_request' => SettingValue::ID_EMAIL,
                    'booking_change' => SettingValue::ID_EMAIL,
                    'account' => SettingValue::ID_EMAIL,
                    'marketting' => SettingValue::ID_EMAIL,
                ]);
            }

            $socialAuth = SocialAccount::where('user_id', $authUser->id)->first();

            return response([
                'data' => new NotificationSettingResource($settings),
                'email' => $authUser->email,
                'isSocialLogin' => $socialAuth ? true : false
            ], StatusCode::HTTP_OK);
        } catch (\Exception $e) {
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
                ->where('password', Hash::make($oldPassword))
                ->first();

            if (!$passwordChangedUser) {
                throw new \Exception('Sorry! old password was not correct.');
            }

            $authUser->password = Hash::make($newPassword);
            $authUser->save();

            return response()->json([
                'data' => "",
                'message' => 'Successfully changed your password.'
            ], StatusCode::HTTP_OK);
        } catch (\Exception $e) {
            return response([
                'error' => [
                    'message' => $e->getMessage()
                ]
            ], StatusCode::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function resetEmail(Request $request)
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
                ->where('password', Hash::make($password))
                ->first();

            if (!$emailChangingUser) {
                throw new \Exception('Sorry! password was incorrect.');
            }

            if ($authUser->email != $email) {
                throw new Exception("Put the correct eamil.");
            }

            $authUser->email = $email;

            $authUser->save();

            return response([
                'data' => [],
                'message' => 'Successfully changed your email.'
            ], StatusCode::HTTP_OK);
        } catch (\Exception $e) {
            return response([
                'error' => [
                    'message' => $e->getMessage()
                ]
            ], StatusCode::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            $notificationSetting = NotificationSetting::where('user_id', Auth::id())
                ->where('id', $id)
                ->first();

            if (!$notificationSetting) {
                throw new Exception("Setting is not found.");
            }
            $notificationSetting->inbox_message = $request['inboxMessage'];
            $notificationSetting->order_message = $request['orderMessage'];
            $notificationSetting->order_update = $request['orderUpdate'];
            $notificationSetting->booking_request = $request['bookingRequest'];
            $notificationSetting->booking_change = $request['bookingChange'];
            $notificationSetting->account = $request['account'];
            $notificationSetting->marketting = $request['marketting'];
            $notificationSetting->save();

            return response([
                'data' => []
            ], StatusCode::HTTP_OK);
        } catch (\Exception $e) {
            return response([
                'error' => [
                    'message' => $e->getMessage()
                ]
            ], StatusCode::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
