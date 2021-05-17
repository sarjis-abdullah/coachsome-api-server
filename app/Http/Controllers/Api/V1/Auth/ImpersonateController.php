<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Data\Constants;
use App\Data\StatusCode;
use App\Entities\Impersonate;
use App\Entities\User;
use App\Http\Controllers\Controller;
use App\Services\TokenService;
use App\Services\UserService;
use Illuminate\Http\Request;
use Auth;
use Illuminate\Support\Facades\Log;

class ImpersonateController extends Controller
{
    /**
     * Impersonate the user.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function impersonate($id)
    {
        $response = [];
        $originalUser = Auth::user();
        $switchedUser = User::find($id);

        try {
            if(!$originalUser || !$switchedUser){
                $statusCode = Constants::HTTP_UNPROCESSABLE_ENTITY;
                throw new \Exception('User not found');
            }

            if($originalUser->id == $switchedUser->id){
                $statusCode = Constants::HTTP_UNPROCESSABLE_ENTITY;
                throw new \Exception('You can not switch yourself.');
            }

            $tokenService = new TokenService();
            $userService = new UserService();

            $tokenService->deleteUserAccessToken($originalUser);

            $userInfo = $userService->getUserInformation($switchedUser, true);
            $accessToken = $tokenService->createUserAccessToken($switchedUser);

            $mImpersonate = new Impersonate();
            $mImpersonate->original_user_id = $originalUser->id;
            $mImpersonate->access_token = $accessToken;
            $mImpersonate->save();

            $response['status'] = 'success';
            $response['user'] = $userInfo;
            $response['accessToken'] = $accessToken;
            return response()->json($response, Constants::HTTP_OK);
        } catch (\Exception $e){
            $response['status'] = 'error';
            $response['message'] = $e->getMessage();
            return response()->json($response, StatusCode::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Revert to the original user.
     *
     * @return \Illuminate\Http\Response
     */
    public function revert(Request $request)
    {
        $response = [];
        $switchedUserAccessToken = $request->bearerToken();
        if($switchedUserAccessToken){
            $impersonate = Impersonate::where('access_token', $switchedUserAccessToken)->first();
            if($impersonate) {
                $tokenService = new TokenService();
                $userService = new UserService();

                $originalUser = User::find($impersonate->original_user_id);
                $switchedUser = Auth::user();

                $impersonate->delete();

                $tokenService->deleteUserAccessToken($switchedUser);

                $response['user'] = $userService->getUserInformation($originalUser);
                $response['accessToken'] = $tokenService->createUserAccessToken($originalUser);
            }
        }

        return $response;
    }
}
