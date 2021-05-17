<?php


namespace App\Http\Controllers;

use App\Data\Constants;
use App\Entities\Profile;
use App\Entities\Role;
use App\Entities\SocialAccount;
use App\Entities\User;
use App\Events\UserRegisteredEvent;
use App\Helpers\Util;
use App\Services\TokenService;
use App\Services\UserService;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Session;

class SocialAuthController extends Controller
{
    public function redirectToProvider(Request $request, $provider)
    {
        // Set user type so that we can decide the user role
        session(['user_type' => $request->query('user_type')]);
        return Socialite::driver($provider)->redirect();
    }

    public function handleProviderCallback($provider, Request $request)
    {

        if (!$request->input('code')) {
            $status = 'error';
            if($provider == 'facebook'){
                $messageKey = 'facebook_error_cancel_message';
            } else {
                $messageKey = 'google_error_cancel_message';
            }
            return redirect(
                env('APP_CLIENT_DOMAIN')
                . '/pages/login?status='
                . $status
                . '&'
                .'message_key='
                .$messageKey);
        }

        $providerUser = Socialite::driver($provider)->user();

        $user = $this->findOrCreateUser(
            $providerUser,
            $provider
        );

        if ($user) {
            UserRegisteredEvent::dispatch($user,session('user_type'), true);
            $tokenService = new TokenService();
            $accessToken = $tokenService->createUserAccessToken($user);
            return redirect(
                env('APP_CLIENT_DOMAIN')
                . '/pages/redirect?access_token='
                . $accessToken
            );
        } else {
            $status = 'error';
            $messageKey = 'provider_error_message_without_email';
            return redirect(
                env('APP_CLIENT_DOMAIN')
                . '/pages/login?status='
                . $status
                . '&'
                .'message_key='
                .$messageKey
            );
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
                    $util = new Util();
                    $user = new User();

                    $fullName = explode(" ", $providerName);
                    $user->first_name = array_key_exists(0, $fullName) ? $fullName[0] : '';
                    $user->last_name = array_key_exists(1, $fullName) ? $fullName[1] : '';
                    $user->email = $providerEmail;
                    $user->user_name = $util->getUserName($user->first_name, $user->last_name);
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
