<?php

namespace App\Http\Controllers\Api\V1\PWA\Auth;

use App\Data\StatusCode;
use App\Entities\OTP;
use App\Entities\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class ForgotPasswordController extends Controller
{




    /**
     * Send a reset OTP to the given user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function sendResetOTP(Request $request)
    {
        try {
            $this->validate($request, [
                'email' => 'required|email|exists:users,email',
            ]);

            $user = User::with('socialAccount')->where('email', $request->email)->first();

            if(!empty($user)){

                if(!empty($user->socialAccount)){
                    throw new \Exception("You had registered yourself using ".$user->socialAccount->provider_name.". Please try login using ".$user->socialAccount->provider_name."!");
                }

                if($user->verified != 1){
                    throw new \Exception("You're not a verified user!");
                }

                $otp = $this->newOtp();

                OTP::updateOrCreate(
                    [
                        'email' => $request->email,
                        'type'  => 'password_reset'
                    ],
                    [
                        'otp'   => $otp,
                    ]
                );
    
                $user->sendPasswordResetNotificationPWA($otp);
    
    
                return response()->json([
                    'message' => 'Password reset email sent successfully.',
                ], StatusCode::HTTP_OK);

            }else{
                throw new \Exception("You're not a registered user. Please register first!");
            }

        } catch (\Exception $e) {
            return response()->json([
                'message'=> $e->getMessage(),
            ], StatusCode::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Validate the given OTP.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function otpValidation(Request $request)
    {

        try {
            $validator = Validator::make($request->all(), [
                'email' => "required",
                'otp' => "required",
            ]);

            if ($validator->fails()) {
                throw new \Exception("Doesn't match!");
            }

            $otp_exists = OTP::where('email', $request->email)->where('otp', $request->otp)->where('type', 'password_reset')->exists();

            if($otp_exists){

                $data['message'] = 'Your email has been verified successfully. You can change your password now!';

                return response($data, StatusCode::HTTP_OK);
            }else{
                throw new \Exception("OTP doesn't match!");
            }

            
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], StatusCode::HTTP_UNPROCESSABLE_ENTITY);
        }

    }

    


    public function newOtp(){
        $otp = rand(1000,9999);
        $otp_exists = OTP::where('otp', $otp)->where('type', 'password_reset')->exists();
        if($otp_exists){
            $otp = $this->newOtp();
        }
        return $otp;
    }

}
