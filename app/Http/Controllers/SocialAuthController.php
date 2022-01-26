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
    const VALUE_IDENTIFY = 'security_identify';

    public function redirectToProvider(Request $request, $provider)
    {
        // Set user type so that we can decide the user role
        // Request from params identify the request comes from the client
        session([
            self::KEY_USER_TYPE => $request->query(self::KEY_USER_TYPE),
            self::KEY_ACTION => $request->query(self::KEY_ACTION),
            self::KEY_USER_ID => $request->query(self::KEY_USER_ID)
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
            } else {
                $messageKey = 'google_error_cancel_message';
            }
            return redirect(
                config('company.url.client')
                . '/login?status='
                . $status
                . '&'
                . 'message_key='
                . $messageKey);
        }

        // When request comes form a specific page
        if (session(self::KEY_ACTION) == self::VALUE_IDENTIFY && session(self::KEY_USER_ID)) {
            // Store verification information according to provider
            $user = User::find(self::KEY_USER_ID);
            if ($user) {
                $userVerification = UserVerification::where('user_id', $user->id)->first();
                if (!$userVerification) {
                    $userVerification = new UserVerification();
                    $userVerification->user_id = $user->id;
                }

                if ($provider == 'facebook') {
                    $userVerification->facebook_connected_at = Carbon::now();
                }

                if ($provider == 'google') {
                    $userVerification->google_connected_at = Carbon::now();
                }

                $userVerification->save();
            }
            return redirect(
                config('company.url.client')
                . '/redirect?'
                . 'request_from='
                . session(self::KEY_ACTION)
            );
        }

        // When login or register as a user
        if (session(self::KEY_USER_TYPE)) {
            $providerUser = Socialite::driver($provider)->user();
            $user = $this->findOrCreateUser(
                $providerUser,
                $provider
            );

            if ($user) {
                UserRegisteredEvent::dispatch($user, session(self::KEY_USER_TYPE), true);
                $tokenService = new TokenService();
                $accessToken = $tokenService->createUserAccessToken($user);
                return redirect(
                    config('company.url.client')
                    . '/redirect?access_token='
                    . $accessToken
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
