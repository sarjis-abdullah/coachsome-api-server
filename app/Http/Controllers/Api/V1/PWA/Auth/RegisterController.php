<?php

namespace App\Http\Controllers\Api\V1\PWA\Auth;

use App\Data\Constants;
use App\Data\StatusCode;
use App\Entities\OTP;
use App\Entities\Profile;
use App\Entities\Role;
use App\Entities\User;
use App\Entities\VerifyUser;
use App\Events\UserRegisteredEvent;
use App\Http\Controllers\Controller;
use App\Helpers\Util;
use App\Services\TranslationService;
use App\Services\UserService;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Ramsey\Uuid\Uuid;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    public $successStatus = 200;
    protected $translationService;

    /**
     * Where to redirect users after registration.
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
        $this->middleware('guest');
        $this->translationService = new TranslationService();
    }


    /**
     * Handle a registration request for the application.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {

        try {
            $data = [];

            $validator = Validator::make($request->all(), [
                'email' => "unique:users,email",
            ]);

            if ($validator->fails()) {
                throw new \Exception("We've already sent a verification code to this email before. please check!");
            }
            $locale = App::currentLocale();
            $translation = $this->translationService->getKeyByLanguageCode($locale);

            $otp = $this->newOtp();

            $beautymail = app()->make(\Snowfire\Beautymail\Beautymail::class);

            OTP::create([
                'email' => $request->email,
                'otp' => $otp
            ]);

            $beautymail->send('emails.PWA.verifyEmail',
                [
                    'otp' => $otp,
                    'translation' => $translation
                ],
                function ($message) use ($request) {
                    $message
                        ->from(config('mail.from.address'))
                        ->to($request->email)
                        ->subject('Email Verification');
                });


            $data['message'] = 'Verification email has been sent, successfully..';

            return response($data, StatusCode::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], StatusCode::HTTP_UNPROCESSABLE_ENTITY);
        }

    }

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

            $otp_exists = OTP::where('email', $request->email)->where('otp', $request->otp)->exists();

            if($otp_exists){

                OTP::where('email', $request->email)->where('otp', $request->otp)->delete();

                $data['message'] = 'Your email has been verified successfully. you can register now!';

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

    public function postRegister(Request $request)
    {

        try {
            $data = [];

            $validator = Validator::make($request->all(), [
                'first_name' => 'required',
                'last_name' => 'required',
                'email' => "unique:users,email",
                'password' => 'required',
            ]);

            if ($validator->fails()) {
                throw new \Exception("This email already exist.");
            }


            $userService = new UserService();

            $user = new User;
            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->email = $request->email;
            $user->user_name = $userService->generateUserName($user->first_name, $user->last_name);
            $user->password = $userService->generateUserHashPassword($request->password);
            $user->save();

            if ($user) {
                $data['status'] = 'success';
                $data['message'] = 'Successfully registered.';
            } else {
                throw new \Exception('Something went wrong, try again.');
            }

            return response($data, StatusCode::HTTP_OK);
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], StatusCode::HTTP_UNPROCESSABLE_ENTITY);
        }

    }

    public function attachUserRole(Request $request){
        $data = [];
        $role = Role::where('name', $request->user_type)->first();
        $user = User::where('email', $request->email)->first();
        if($user && $role){
            $user->attachRole($role);
            $data['status'] = 'success';
            $data['message'] = 'Congrats! You have joined Coachsome as, '.$role->display_name;
        }else {
            throw new \Exception('Something went wrong, try again.');
        }
        return response($data, StatusCode::HTTP_OK);
    }

    public function newOtp(){
        $otp = rand(1000,9999);
        $otp_exists = OTP::where('otp', $otp)->exists();
        if($otp_exists){
            $otp = $this->newOtp();
        }
        return $otp;
    }

    /*
    * Details api
    *
    * @return \Illuminate\Http\Response
    */
    public function details()
    {
        $user = Auth::user();
        return response()->json(['success' => $user], $this->successStatus);
    }

    /**
     * Get the guard to be used during registration.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard();
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param array $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
    }
}
