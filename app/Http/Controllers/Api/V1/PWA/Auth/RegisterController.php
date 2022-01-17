<?php

namespace App\Http\Controllers\Api\V1\PWA\Auth;

use App\Data\Constants;
use App\Data\StatusCode;
use App\Entities\Profile;
use App\Entities\User;
use App\Entities\VerifyUser;
use App\Events\UserRegisteredEvent;
use App\Http\Controllers\Controller;
use App\Helpers\Util;
use App\Services\TranslationService;
use App\Services\UserService;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Auth\Events\Registered;
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
                throw new \Exception("This email already exist.");
            }
            $locale = App::currentLocale();
            $translation = $this->translationService->getKeyByLanguageCode($locale);
            $token = Uuid::uuid1()->toString();
            $link = env('APP_PWA_DOMAIN_EMAIL_VERIFICATION_URL') . '?token=' . $token.'&&email=' .$request->email ;

            $beautymail = app()->make(\Snowfire\Beautymail\Beautymail::class);
            $beautymail->send('emails.PWA.verifyEmail',
                [
                    'link' => $link,
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
