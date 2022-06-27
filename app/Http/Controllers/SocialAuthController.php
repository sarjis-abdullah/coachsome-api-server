<?php


namespace App\Http\Controllers;

use App\Data\Constants;
use App\Entities\Profile;
use App\Entities\Role;
use App\Entities\SocialAccount;
use App\Entities\User;
use App\Entities\UserVerification;
use App\Events\UserRegisteredEvent;
use App\Services\TokenService;
use App\Services\UserService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Session;

class SocialAuthController extends Controller
{
    const KEY_ACTION = 'action';
    const KEY_USER_TYPE = 'user_type';
    const KEY_USER_ID = 'user_id';
    const KEY_PWA = 'pwa';

    const VALUE_IDENTIFY = 'security_identify';
    const VALUE_PWA_AUTH = 'pwa_auth';
    const VALUE_PWA_IDENTIFY = 'pwa_identify';

    public function redirectToProvider(Request $request, $provider)
    {
        // Set user type so that we can decide the user role
        // Request from params identify the request comes from the client
        session([
            self::KEY_USER_TYPE => $request->query(self::KEY_USER_TYPE),
            self::KEY_ACTION => $request->query(self::KEY_ACTION),
            self::KEY_USER_ID => $request->query(self::KEY_USER_ID),
            self::KEY_PWA => $request->query(self::KEY_PWA)
        ]);

        // Redirect to provider
        return Socialite::driver($provider)->redirect();
    }

    public function handleProviderCallback($provider, Request $request)
    {
        // if error happened from provider
        if (!$request->input('code')) {
            $status = 'error';
            if ($provider == 'facebook') {
                $messageKey = 'facebook_error_cancel_message';
            }else if($provider == 'apple'){
                $messageKey = 'apple_error_cancel_message';
            }else {
                $messageKey = 'google_error_cancel_message';
            }

            if(session(self::KEY_PWA) == self::VALUE_PWA_AUTH){
                return redirect(
                    config('company.url.pwa')
                    . '/login?status='
                    . $status
                    . '&'
                    . 'message_key='
                    . $messageKey);
            } else {
                return redirect(
                    config('company.url.client')
                    . '/login?status='
                    . $status
                    . '&'
                    . 'message_key='
                    . $messageKey);
            }

        }

        // PWA auth
        if (session(self::KEY_PWA) == self::VALUE_PWA_AUTH) {
            $isExisting = false;
            $tokenService = new TokenService();

            $providerUser = Socialite::driver($provider)->user();
            $providerEmail = $providerUser->getEmail();

            $user = User::where('email', $providerEmail)->first();
            if($user){
                $isExisting = true;
            } else {
                $user = $this->findOrCreateUser(
                    $providerUser,
                    $provider
                );
                if (session(self::KEY_USER_TYPE)) {
                    if ($user) {
                        $isExisting = true;
                        $role = Role::where('name', $request->user_type)->first();
                        if($user && $role){
                            $user->attachRole($role);
                        }
                    }
                }
            }

            $accessToken = $tokenService->createUserAccessToken($user);

            return redirect(
                config('company.url.pwa')
                . '/redirect?access_token='
                . $accessToken
                .'&is_existing='
                .$isExisting
            );
        }

        // When request comes form a specific page
        if (session(self::KEY_ACTION) == self::VALUE_IDENTIFY && session(self::KEY_USER_ID)) {
            // Store verification information according to provider
            $user = User::find(session(self::KEY_USER_ID));
            if ($user) {
                $userVerification = UserVerification::where('user_id', $user->id)->first();
                if (!$userVerification) {
                    $userVerification = new UserVerification();
                    $userVerification->user_id = $user->id;
                }

                if ($provider == 'facebook') {
                    $userVerification->facebook_connected_at = Carbon::now();
                }

                if ($provider == 'apple') {
                    $userVerification->facebook_connected_at = Carbon::now();
                }

                if ($provider == 'google') {
                    $userVerification->google_connected_at = Carbon::now();
                }

                $userVerification->save();
            }

            
            if (session(self::KEY_PWA) == self::VALUE_PWA_IDENTIFY) {
                // redirect to pwa after security verification
                return redirect(
                    config('company.url.pwa')
                    . '/redirect?'
                    . self::KEY_ACTION
                    . '='
                    . session(self::KEY_ACTION)
                );

            }else{
                // redirect to main site after security verification
                return redirect(
                    config('company.url.client')
                    . '/redirect?'
                    . self::KEY_ACTION
                    . '='
                    . session(self::KEY_ACTION)
                );

            }
        }

        // When login or register as a user
        if (session(self::KEY_USER_TYPE)) {
            $isExisting = false;
            $providerUser = Socialite::driver($provider)->user();
            $user = $this->findOrCreateUser(
                $providerUser,
                $provider
            );

            if ($user) {
                $isExisting = true;
                // UserRegisteredEvent::dispatch($user, session(self::KEY_USER_TYPE), true);
                $role = Role::where('name', $request->user_type)->first();
                if($user && $role){
                    $user->attachRole($role);
                }
                $tokenService = new TokenService();
                $accessToken = $tokenService->createUserAccessToken($user);
                return redirect(
                    config('company.url.client')
                    . '/redirect?access_token='
                    . $accessToken
                    .'&is_existing='
                    .$isExisting
                );
            } else {
                $status = 'error';
                $messageKey = 'provider_error_message_without_email';
                return redirect(
                    config('company.url.client')
                    . '/login?status='
                    . $status
                    . '&'
                    . 'message_key='
                    . $messageKey
                );
            }
        }

    }

    public function handleAppleCallback(Request $request)
    {
        $provider = 'apple';
        $token = $request->token;

        // if error happened from provider
        // if (!$request->input('code')) {
        //     $status = 'error';
        //     if ($provider == 'facebook') {
        //         $messageKey = 'facebook_error_cancel_message';
        //     }else if($provider == 'apple'){
        //         $messageKey = 'apple_error_cancel_message';
        //     }else {
        //         $messageKey = 'google_error_cancel_message';
        //     }

        //     if(session(self::KEY_PWA) == self::VALUE_PWA_AUTH){
        //         return redirect(
        //             config('company.url.pwa')
        //             . '/login?status='
        //             . $status
        //             . '&'
        //             . 'message_key='
        //             . $messageKey);
        //     } else {
        //         return redirect(
        //             config('company.url.client')
        //             . '/login?status='
        //             . $status
        //             . '&'
        //             . 'message_key='
        //             . $messageKey);
        //     }

        // }

        // PWA auth
        if (session(self::KEY_PWA) == self::VALUE_PWA_AUTH) {
            $isExisting = false;
            $tokenService = new TokenService();

            $providerUser = Socialite::driver($provider)->userFromToken($token); //Socialite::driver($provider)->user();
            $providerEmail = $providerUser->getEmail();

            $user = User::where('email', $providerEmail)->first();
            if($user){
                $isExisting = true;
            } else {
                $user = $this->findOrCreateUser(
                    $providerUser,
                    $provider
                );
            }

            $accessToken = $tokenService->createUserAccessToken($user);

            return redirect(
                config('company.url.pwa')
                . '/redirect?access_token='
                . $accessToken
                .'&is_existing='
                .$isExisting
            );
        }

        // When request comes form a specific page
        if (session(self::KEY_ACTION) == self::VALUE_IDENTIFY && session(self::KEY_USER_ID)) {
            // Store verification information according to provider
            $user = User::find(session(self::KEY_USER_ID));
            if ($user) {
                $userVerification = UserVerification::where('user_id', $user->id)->first();
                if (!$userVerification) {
                    $userVerification = new UserVerification();
                    $userVerification->user_id = $user->id;
                }

                if ($provider == 'facebook') {
                    $userVerification->facebook_connected_at = Carbon::now();
                }

                if ($provider == 'apple') {
                    $userVerification->facebook_connected_at = Carbon::now();
                }

                if ($provider == 'google') {
                    $userVerification->google_connected_at = Carbon::now();
                }

                $userVerification->save();
            }

            
            if (session(self::KEY_PWA) == self::VALUE_PWA_IDENTIFY) {
                // redirect to pwa after security verification
                return redirect(
                    config('company.url.pwa')
                    . '/redirect?'
                    . self::KEY_ACTION
                    . '='
                    . session(self::KEY_ACTION)
                );

            }else{
                // redirect to main site after security verification
                return redirect(
                    config('company.url.client')
                    . '/redirect?'
                    . self::KEY_ACTION
                    . '='
                    . session(self::KEY_ACTION)
                );

            }
        }

        // When login or register as a user
        if (session(self::KEY_USER_TYPE)) {
            $isExisting = false;
            $providerUser = Socialite::driver($provider)->user();
            $user = $this->findOrCreateUser(
                $providerUser,
                $provider
            );

            if ($user) {
                $isExisting = true;
                // UserRegisteredEvent::dispatch($user, session(self::KEY_USER_TYPE), true);
                $role = Role::where('name', $request->user_type)->first();
                if($user && $role){
                    $user->attachRole($role);
                }
                $tokenService = new TokenService();
                $accessToken = $tokenService->createUserAccessToken($user);
                return redirect(
                    config('company.url.client')
                    . '/redirect?access_token='
                    . $accessToken
                    .'&is_existing='
                    .$isExisting
                );
            } else {
                $status = 'error';
                $messageKey = 'provider_error_message_without_email';
                return redirect(
                    config('company.url.client')
                    . '/login?status='
                    . $status
                    . '&'
                    . 'message_key='
                    . $messageKey
                );
            }
        }





        // $client = DB::table('oauth_clients')
        //     ->where('password_client', true)
        //     ->first();
        // if (!$client) {
        //     return response()->json([
        //         'message' => trans('validation.passport.client_error'),
        //         'status' => Response::HTTP_INTERNAL_SERVER_ERROR
        //     ], Response::HTTP_INTERNAL_SERVER_ERROR);
        // }

        // $data = [
        //     'grant_type' => 'social',
        //     'client_id' => $client->id,
        //     'client_secret' => $client->secret,
        //     'provider' => 'apple',
        //     'access_token' => $token
        // ];
        // $request = Request::create('/oauth/token', 'POST', $data);

        // $content = json_decode(app()->handle($request)->getContent());
        // if (isset($content->error) && $content->error === 'invalid_request') {
        //     return response()->json(['error' => true, 'message' => $content->message]);
        // }

        // return response()->json(
        //     [
        //         'error' => false,
        //         'data' => [
        //             'user' => $user,
        //             'meta' => [
        //                 'token' => $content->access_token,
        //                 'expired_at' => $content->expires_in,
        //                 'refresh_token' => $content->refresh_token,
        //                 'type' => 'Bearer'
        //             ],
        //         ]
        //     ],
        //     Response::HTTP_OK
        // );
    }

    public function findOrCreateUser($providerUser, $provider)
    {
        $user = null;

        $account = SocialAccount::where('provider_name', $provider)
            ->where('provider_id', $providerUser->getId())
            ->first();

        if ($account) {
            $user = $account->user;
        }

        if (!$account) {

            $providerEmail = $providerUser->getEmail();
            $providerName = $providerUser->getName();

            if ($providerEmail) {
                $user = User::where('email', $providerEmail)->first();
                if (!$user) {
                    $userService = new UserService();;
                    $user = new User();

                    $fullName = explode(" ", $providerName);
                    $user->first_name = array_key_exists(0, $fullName) ? $fullName[0] : '';
                    $user->last_name = array_key_exists(1, $fullName) ? $fullName[1] : '';
                    $user->email = $providerEmail;
                    $user->user_name = $userService->generateUserName($user->first_name, $user->last_name);
                    $user->save();
                }

                // Create social account
                SocialAccount::create([
                    'user_id' => $user->id,
                    'provider_id' => $providerUser->getId(),
                    'provider_name' => $provider,
                ]);
            }
        }

        return $user;
    }
}
