<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Entities\ProfileSwitch;
use App\Entities\Role;
use App\Data\Constants;
use App\Data\StatusCode;
use App\Entities\User;
use App\Http\Controllers\Controller;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileSwitchingController extends Controller
{
    public function switch(Request $request){

        $response = [];
        $is_admin_switched = $request->is_admin_switched;

        try {
            $user = Auth::user();
            $userRole = Auth::user()->roles[0];
            $requestedRole = Role::where('name', $request->role)->first();

            $existedSwitch = ProfileSwitch::where('user_id', $user->id)->exists();

            if(!$existedSwitch){
                $profileSwitch = new ProfileSwitch();
                $profileSwitch->user_id = $user->id;
                $profileSwitch->original_role = $userRole->id;
                $profileSwitch->is_switched = 0;
                $profileSwitch->save();
                $profileSwitchData = [];
            }

            if($user->hasRole($request->role)){
                $statusCode = Constants::HTTP_UNPROCESSABLE_ENTITY;
                $profileSwitchData = [];
                throw new \Exception('You are already in this role');
            }else{

                $user->syncRoles([$request->role]);

                $profileSwitchData = ProfileSwitch::where('user_id', $user->id)->first();
                $profileSwitchData->is_switched = 1;
                $profileSwitchData->switched_role = $requestedRole->id;
                $profileSwitchData->switch_from = $userRole->name;
                $profileSwitchData->switch_to = $requestedRole->name;
                $profileSwitchData->save();
            }

            $userService = new UserService();

            $userData = User::where('id', $user->id)->first();

            $userInfo = $userService->getUserInformation($userData, $is_admin_switched);

            // $switchInfo = ProfileSwitch::where('user_id', $user->id)->first();
            // $original_role = Role::where('id' , $switchInfo->original_role)->first()->name;

            // $userInfo->is_profile_switched = !empty($profileSwitchData) && $profileSwitchData->is_switched ? true : false;
            // $userInfo->original_role = $original_role;
            // $userInfo->profile_switched_to = !empty($profileSwitchData) && $profileSwitchData->switch_to ? $profileSwitchData->switch_to : null;

            $response['status'] = 'success';
            $response['switchData'] = $profileSwitchData;
            $response['user'] = $userInfo;

            return response()->json($response, Constants::HTTP_OK);
        } catch (\Exception $e){
            $response['status'] = 'error';
            $response['message'] = $e->getMessage();
            return response()->json($response, StatusCode::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function switchInfo(){
        $response = [];
        try {
            $user = Auth::user();
            $existedSwitch = ProfileSwitch::where('user_id', $user->id)->exists();
            $response['status'] = 'success';
            $response['user_id'] = $user->id;
            $response['is_profile_switched'] = $existedSwitch;

            return response()->json($response, Constants::HTTP_OK);
        } catch (\Exception $e){
            $response['status'] = 'error';
            $response['message'] = $e->getMessage();
            return response()->json($response, StatusCode::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
