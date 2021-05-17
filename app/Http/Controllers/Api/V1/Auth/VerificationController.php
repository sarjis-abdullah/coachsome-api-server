<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Entities\VerifyUser;
use App\Http\Controllers\Controller;
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
        $data = [];
        $code = 200;
        $veryUser = VerifyUser::where('token', $request->token)->first();
        if ($veryUser) {
            $user = $veryUser->user;
            $user->verified = 1;
            if ($user->save()) {
                $veryUser->delete();
            }
            $data['status'] = 'success';
            $data['message'] = 'Successfully verify your account';
        } else {
            $data['status'] = 'error';
            $data['message'] = 'Token not found';
            $code = 401;
        }

        return response()->json($data, $code);
    }

}
