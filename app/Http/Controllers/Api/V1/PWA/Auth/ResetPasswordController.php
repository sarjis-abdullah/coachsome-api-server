<?php

namespace App\Http\Controllers\Api\V1\PWA\Auth;

use App\Data\StatusCode;
use App\Entities\OTP;
use App\Entities\User;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ResetsPasswords;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;

class ResetPasswordController extends Controller
{

    public function otpExist(Request $request){
        $data['otp_exist'] = false;
        $otp_exists = OTP::where('email', $request->email)->where('type', 'password_reset')->exists();
        if($otp_exists){
            $data['otp_exist'] = true;
        }
        
        return response($data, StatusCode::HTTP_OK);
    }


    /**
     * Reset the given user's password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function reset(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        if ($validator->fails()) {
            throw new \Exception("Something went wrong, Please try again!");
        }

        $otp_exists = OTP::where('email', $request->email)->where('type', 'password_reset')->exists();

        if($otp_exists){
            $password = Hash::make($request->password);
            $user = User::where('email', $request->email)->first();
            $user->password = $password;
            if($user->save()){
                OTP::where('email', $request->email)->where('type', 'password_reset')->delete();
                $data['message'] = "Password has been updated successfully. you can now login with your new password";
                return response($data, StatusCode::HTTP_OK);
            }else{
                $data['message'] = "Password update failed";
                return response($data, StatusCode::HTTP_UNPROCESSABLE_ENTITY);
            }
        }else{
            throw new \Exception("Please verify your OTP first to reset!");
        }
        
    }

    /**
     * Get the response for a successful password reset.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $response
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    protected function sendResetResponse(Request $request, $response)
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Successfully reset your password.',
            'data' => $response
        ]);
    }

    /**
     * Get the response for a failed password reset.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $response
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    protected function sendResetFailedResponse(Request $request, $response)
    {
        return response()->json([
            'status' => 'error',
            'message' => 'Something wrong, try again.',
            'data' => $response
        ]);
    }
}
