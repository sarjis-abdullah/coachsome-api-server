<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Data\Constants;
use App\Entities\User;
use App\Http\Controllers\Controller;
use App\Services\TokenService;
use App\Services\UserService;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AdminLoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
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
        $this->middleware('guest')->except('logout');

    }

    /**
     * Handle a login request to the application.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(Request $request)
    {
        $response = [];

        $request->validate([
            'email' => 'required|email|max:255',
        ]);

        try {
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                throw new \Exception('This email does not exist');
            }


            if (!$user->hasRole([Constants::ROLE_KEY_STAFF,Constants::ROLE_KEY_SUPER_ADMIN,Constants::ROLE_KEY_ADMIN])) {
                throw new \Exception('You are not staff.');
            }

            if (Auth::attempt(['email' => request('email'), 'password' => request('password')])) {
                $userService = new UserService();
                $user = Auth::user();
                $tokenService = new TokenService();
                $response['user'] = $userService->getUserInformation($user);
                $response['access_token'] = $tokenService->createUserAccessToken($user);
                $response['status'] = 'success';
                return response()->json($response, Constants::HTTP_OK);
            } else {
                $response['status'] = 'error';
                throw new \Exception('Your credential does not correct.');
            }


        } catch (\Exception $e) {
            if ($e instanceof ValidationException) {
                $response['status'] = 'error';
                $response['message'] = $e->validator->errors()->first();
                return response()->json($response, Constants::HTTP_UNPROCESSABLE_ENTITY);
            }

            $response['status'] = 'error';
            $response['message'] = $e->getMessage();
            return response()->json($response, Constants::HTTP_UNPROCESSABLE_ENTITY);
        }

    }

    /**
     * Log the user out of the application.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        $response = [];

        $response['status'] = 'success';
        $response['message'] = 'Successfully log out';

        $tokenService = new TokenService();
        $tokenService->deleteUserAccessToken(Auth::user());

        return $response;
    }
}
