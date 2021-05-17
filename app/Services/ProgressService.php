<?php


namespace App\Services;

use App\Data\Constants;
use App\Entities\User;
use Illuminate\Support\Facades\Log;

class ProgressService
{
    /*
    * Profile
    *  - Profile picture
    *  - Profile name
    *  - An “About” text with a minimum of 150 characters
    *  - Phone number
    *  - Minimum one language
    *  - Minimum one category
    *  - Minimum three tags
   */
    public function getUserProfilePageProgress(User $user)
    {

        $totalStep = 7;
        $completedStep = 0;

        if ($user) {
            $profile = $user->profile;
            if ($profile->image) {
                $completedStep++;
            }

            if ($profile->profile_name) {
                $completedStep++;
            }

            if ($profile->about_me && strlen($profile->about_me) >= 150) {
                $completedStep++;
            }

            if ($profile->mobile_no && $profile->mobile_code) {
                $completedStep++;
            }

            // Language
            if ($user->languages->first()) {
                $completedStep++;
            }

            // Category
            if ($user->sportCategories->first()) {
                $completedStep++;
            }

            // Tag
            if ($user->sportTags()->count() >= 3) {
                $completedStep++;
            }
        }
        return $this->countProgress($totalStep, $completedStep);
    }

    public function getUserPackagePageProgress(User $user)
    {
        $totalStep = 2;
        $completedStep = 0;

        $packages = $user->packages;

        if ($user->ownPackageSetting) {
            $completedStep++;
        }

        if ($packages && $packages->count() > 0) {
            $completedStep++;
        }

        return $this->countProgress($totalStep, $completedStep);

    }

    public function getUserGeographyPageProgress(User $user)
    {
        $totalStep = 1;
        $completedStep = 0;

        if ($user->locations->count() > 0) {
            $completedStep++;
        }

        return $this->countProgress($totalStep, $completedStep);
    }

    public function getUserImageAndVideoPageProgress(User $user)
    {
        $totalStep = 2;
        $completedStep = 0;
        $galleries = $user->galleries ?? null;
        if ($galleries->count() > 0) {
            $galleryImage = $galleries->where('type', Constants::GALLERY_ASSET_TYPE_IMAGE)->first();
            $galleryVideo = $galleries->where('type', Constants::GALLERY_ASSET_TYPE_VIDEO)->first();
            if ($galleryImage) {
                $completedStep++;
            }
            if ($galleryVideo) {
                $completedStep++;
            }
        }
        return $this->countProgress($totalStep, $completedStep);
    }

    public function getUserAvailabilityPageProgress(User $user)
    {
        $totalStep = 1;
        $completedStep = 0;
        $userDefaultAvailability = $user->defaultAvailability;
        if ($userDefaultAvailability) {
            $completedStep++;
        }
        return $this->countProgress($totalStep, $completedStep);
    }

    public function getUserReviewPageProgress(User $user)
    {
        $totalStep = 1;
        $completedStep = 0;

        if ($user->reviews()->count() > 0) {
            $completedStep++;
        }

        return $this->countProgress($totalStep, $completedStep);
    }

    private function countProgress($totalStep, $completedStep)
    {
        return round($completedStep / $totalStep * 100);
    }
}
