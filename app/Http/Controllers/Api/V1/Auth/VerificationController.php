<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Data\StatusCode;
use App\Entities\User;
use App\Entities\UserVerification;
use App\Entities\VerifyUser;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\VerifiesEmails;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Email Verification Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling email verification for any
    | user that recently registered with the application. Emails may also
    | be re-sent if the user didn't receive the original email message.
    |
    */

    use VerifiesEmails;

    /**
     * Where to redirect users after verification.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('auth');
        $this->middleware('signed')->only('verify');
        $this->middleware('throttle:6,1')->only('verify', 'resend');
    }


    public function emailVerify(Request $request)
    {

        try {
            $data = [];

            if($request->has('request_from_pwa')){
                $user =  User::where('email', $request->email)->first();
                if (!$user) {
                    throw new \Exception('User not found');
                }
            }else{
                $veryUser = VerifyUser::where('token', $request->token)->first();
                if (!$veryUser) {
                    throw new \Exception('Token not found');
                }
                $user = $veryUser->user;
            }
            
            
            $user->verified = 1;
            if ($user->save()) {
                if($request->has('token')){
                    $veryUser->delete();
                }
            }

            // User verificaton information
            $userVerification = UserVerification::where('user_id', $user->id)->first();
            if (!$userVerification) {
                $userVerification = new  UserVerification();
                $userVerification->user_id = $user->id;
            }
            $userVerification->email_verified_at = Carbon::now();
            $userVerification->save();
            
            $data['message'] = 'Successfully verify your account';
            return response($data, StatusCode::HTTP_OK);
        } catch (\Exception $e) {
            return response(['message' => $e->getMessage()], StatusCode::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
