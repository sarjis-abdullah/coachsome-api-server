<?php


namespace App\Services;


use App\Entities\Impersonate;
use App\Entities\ProfileSwitch;
use App\Entities\Role;
use App\Entities\SocialAccount;
use App\Entities\User;
use App\Services\Media\MediaService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserService
{

    /**
     * Generate username
     * @param $firstName
     * @param string $lastName
     * @return string
     */
    public function generateUserName($firstName, $lastName = '')
    {
        $userName = '';

        if ($firstName) {
            $userName .= strtolower(explode(" ", $firstName)[0]);
        }

        if ($lastName) {
            $userName .= '.' . strtolower(explode(" ", $lastName)[0]);
        }

        $countUser = User::whereRaw("user_name REGEXP '^{$userName}(\.[0-9]*)?$'")->count();

        if (($countUser + 1) > 1) {
            $suffix = $countUser + 1;
            $userName .= '.' . $suffix;
        }

        return $userName;

    }


    /**
     * @param $originalPassword
     * @return string
     */
    public function generateUserHashPassword($originalPassword)
    {
        return Hash::make($originalPassword);
    }

    /**
     * @param User $user
     * @param bool $isSwitched
     * @return \stdClass|null
     */
    public function getUserInformation(User $user, $isSwitched = false)
    {
        $impersonate = Impersonate::where('access_token', request()->bearerToken())->first();
        if (!$isSwitched && $impersonate) {
            $isSwitched = true;
        }

        if ($user) {

            $original_role = null;

            $switchInfo = ProfileSwitch::where('user_id', $user->id)->first();

            if(!empty($switchInfo)){
                $original_role = Role::where('id' , $switchInfo->original_role)->first()->name;
            }

            $data = new \stdClass();
            $mediaService = new MediaService();

            $images = $mediaService->getImages($user);
            $socialAcc = SocialAccount::where('user_id', $user->id)->first();

            $data->id = $user->id;
            $data->user_name = $user->user_name ?? null;
            $data->first_name = $user->first_name;
            $data->last_name = $user->last_name;
            $data->email = $user->email;
            $data->image = $images['square'] ? $images['square'] : $images['old'];
            $data->roles = $user->roles;
            $data->is_switched = $isSwitched;
            $data->is_active = $user->isActive();
            $data->is_social_account = $socialAcc ? true : false;
            $data->is_profile_switched = !empty($switchInfo) && $switchInfo->is_switched == 1 ? true : false;
            $data->original_role = $original_role;
            $data->profile_switched_to = !empty($switchInfo) && $switchInfo->switch_to ? $switchInfo->switch_to : null;
        }

        return $data;
    }

    /**
     * Active Rules
     * A coach profile can only be active in the marketplace if they have:
     * Profile
     *  - Profile picture
     *  - Profile name
     *  - An “About” text with a minimum of 150 characters
     *  - Phone number
     *  - Minimum one language
     *  - Minimum one category
     *  - Minimum three tags
     * Packages
     *  - An hourly rate
     *  - At least one package
     * Geography
     *  - A least one location
     */
    public function hasPermissionToChangeActiveStatus($user)
    {
        $hasPermission = true;
        $profile = $user->profile;
        if ($profile) {
            if (!$profile->image || !$profile->profile_name || strlen($profile->about_me) <= 149) {
                $hasPermission = false;
            }
        } else {
            $hasPermission = false;
        }

        if ($user->sportCategories()->count() == 0) {
            $hasPermission = false;
        }

        if ($user->sportTags()->count() < 3) {
            $hasPermission = false;
        }


        if ($user->packages()->count() == 0) {
            $hasPermission = false;
        }


        if ($user->locations()->count() == 0) {
            $hasPermission = false;
        }


        return $hasPermission;
    }


}
