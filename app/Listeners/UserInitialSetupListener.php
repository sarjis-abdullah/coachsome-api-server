<?php

namespace App\Listeners;

use App\Data\Constants;
use App\Data\RoleData;
use App\Entities\Profile;
use App\Entities\UserSetting;
use App\Entities\VerifyUser;
use App\Events\UserRegisteredEvent;
use App\Services\ActiveCampaign\ActiveCampaignService;
use App\Services\Locale\LocaleService;
use App\Services\TranslationService;
use App\Services\UserService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use PragmaRX\Countries\Package\Countries;
use Ramsey\Uuid\Uuid;

class UserInitialSetupListener
{
    public function handle(UserRegisteredEvent $event)
    {
        $locale = App::currentLocale();
        $translationService = new TranslationService();
        $activeCampaignService = new ActiveCampaignService();


        $user = $event->user;
        $userType = $event->userType;
        $provider = $event->provider ?? false;

        // Attach role
        if ($user->roles()->count() < 1) {
            if (RoleData::ROLE_KEY_COACH == $userType) {
                $user->attachRole($userType);
            }

            if (RoleData::ROLE_KEY_ATHLETE == $userType) {
                $user->attachRole($userType);
            }
        }

        // Profile setup
        $profile = $user->profile ?? new Profile();
        $profile->user_id = $user->id;
        $profile->profile_name = $user->first_name . ' ' . $user->last_name;
        $profile->user_role = $user->roles[0]->name;
        $profile->is_onboarding = RoleData::ROLE_KEY_COACH == $userType ? 1 : 0;
        $profile->save();

        

        if(env('CURR_ENV') == "production"){
            // Configure active campaign
            $contactRes = $activeCampaignService->createOrUpdateContact([
                "contact" => [
                    "firstName" => $user->first_name,
                    "lastName" => $user->last_name,
                    "email" => $user->email,
                    "phone" => "",
                ]
            ]);
            $data = json_decode($contactRes, true);
            if (RoleData::ROLE_KEY_COACH == $userType) {
                $activeCampaignService->addTagToContact( [
                    'contactTag'=>[
                        'contact' => $data['contact']['id'],
                        'tag' => $activeCampaignService->getCoachTagId(),
                    ]
                ]);
            }
            if (RoleData::ROLE_KEY_ATHLETE == $userType) {
                $activeCampaignService->addTagToContact( [
                    'contactTag'=>[
                        'contact' => $data['contact']['id'],
                        'tag' => $activeCampaignService->getAthleteTagId(),
                    ]
                ]);
            }
        }


        // Setting setup
        $localeService = new LocaleService();
        $settings = new UserSetting();
        $settings->user_id = $user->id;
        $settings->first_name = $user->first_name;
        $settings->last_name = $user->last_name;
        $settings->cca2 = $localeService->currentCountryCode();
        $settings->timezone = $localeService->currentTimezone();
        $settings->save();


        // Verification email
        // if ($provider == false) {
        //     $translation = $translationService->getKeyByLanguageCode($locale);
        //     $token = Uuid::uuid1()->toString();
        //     $link = env('APP_CLIENT_DOMAIN_EMAIL_VERIFICATION_URL') . '?token=' . $token;

        //     VerifyUser::create(['user_id' => $user->id, 'token' => $token]);

        //     $beautymail = app()->make(\Snowfire\Beautymail\Beautymail::class);
        //     $beautymail->send('emails.verifyEmail',
        //         [
        //             'fullName' => $user->fullName(),
        //             'link' => $link,
        //             'translation' => $translation
        //         ],
        //         function ($message) use ($user) {
        //             $message
        //                 ->from(config('mail.from.address'))
        //                 ->to($user->emailAddress(), $user->fullName())
        //                 ->subject('Email Verification');
        //         });
        // }

    }
}
