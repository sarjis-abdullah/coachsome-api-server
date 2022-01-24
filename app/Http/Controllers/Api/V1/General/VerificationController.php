<?php

namespace App\Http\Controllers\Api\V1\General;

use App\Data\StatusCode;
use App\Entities\VerifyUser;
use App\Http\Controllers\Controller;
use App\Services\TranslationService;
use Faker\Provider\Uuid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class VerificationController extends Controller
{

    public function verifyEmail(Request $request)
    {
        try {
            $locale = App::currentLocale();
            $translationService = new TranslationService();
            $user = Auth::user();
            $translation = $translationService->getKeyByLanguageCode($locale);
            $token = Str::uuid()->toString();
            $link = config('company.url.email_verification_page') . '?token=' . $token;

            VerifyUser::create(['user_id' => $user->id, 'token' => $token]);

            $beautymail = app()->make(\Snowfire\Beautymail\Beautymail::class);
            $beautymail->send(
                'emails.verifyEmail',
                [
                    'fullName' => $user->fullName(),
                    'link' => $link,
                    'translation' => $translation
                ],
                function ($message) use ($user) {
                    $message
                        ->from(config('mail.from.address'))
                        ->to($user->email, $user->fullName())
                        ->subject('Email Verification');
                }
            );
            return response([
                'data' => []
            ], StatusCode::HTTP_OK);
        } catch (\Exception $e) {
            return response([
                'error'=>[
                    'message' => $e->getMessage()
                ]
            ], StatusCode::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function verifyPhone()
    {
        //
    }

    public function verifyFacebook()
    {
        //
    }

    public function verifyGoogle()
    {
        return Socialite::driver('google')->getTargetUrl();
    }

    public function verifyTwitter()
    {
        //
    }
}
