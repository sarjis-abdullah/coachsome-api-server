<?php

namespace App\Http\Controllers\Api\V1\Auth;

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
            $validator = Validator::make($request->all(), [
                'first_name' => 'required',
                'last_name' => 'required',
                'user_type' => 'required',
                'email' => "unique:users,email",
                'password' => 'required',
            ]);

            if ($validator->fails()) {
                throw new \Exception("This email already exist.");
            }

            $data = [];

            $userService = new UserService();

            $user = new User;
            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->email = $request->email;
            $user->user_name = $userService->generateUserName($user->first_name, $user->last_name);
            $user->password = $userService->generateUserHashPassword($request->password);
            $user->save();

            if ($user) {
                UserRegisteredEvent::dispatch($user, $request->user_type);
                $data['status'] = 'success';
                $data['message'] = 'Successfully registered.';
                return response()->json($data, StatusCode::HTTP_OK);
            } else {
                throw new \Exception('Something went wrong, try again.');
            }

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], StatusCode::HTTP_CONFLICT);
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
