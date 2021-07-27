<?php


namespace App\Http\Controllers\Api\V1\General;


use App\Data\StatusCode;
use App\Entities\User;
use App\Services\Mixpanel\MixpanelService;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UserController
{
    public function updateUserName($userName, Request $request)
    {
        $response = [];
        $user = Auth::user();
        $existUser = User::where('user_name', $userName)->first();

        if($existUser){
            if($existUser->id == $user->id){
                $response['status'] = 'success';
                $response['message'] = 'Successfully saved your name.';
            } else {
                $response['status'] = 'error';
                $response['message'] = 'The name is already used.';
            }
        } else {
            $user->user_name = $userName;
            if($user->save()){
                $response['status'] = 'success';
                $response['message'] = 'Successfully saved your name.';
            } else{
                $response['status'] = 'error';
                $response['message'] = 'Something went wrong, try again';
            }
        }

        return $response;
    }

    public function getAuthUserInformation(Request $request)
    {

        $isSwitched = $request->query('is_switched') ? $request->query('is_switched') : false;

        $response = [];
        $authUser = Auth::user();

        if($authUser){
            $userService = new UserService();
            $response['status'] = 'success';
            $response['message'] = 'Successfully get the user information';
            $response['user'] = $userService->getUserInformation($authUser, $isSwitched);
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Something went wrong, try again';
        }
        return response()->json($response, StatusCode::HTTP_OK);
    }
}
