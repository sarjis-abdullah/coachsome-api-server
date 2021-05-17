<?php


namespace App\Services;


use App\Data\Constants;
use Illuminate\Support\Facades\Auth;

class MarketplaceService
{
    public function isUserActive($user)
    {
        $isActive = false;

        if ($user->activity_status_id == Constants::ACTIVITY_STATUS_ID_ACTIVE) {
            $isActive = true;
        }

        return $isActive;
    }

    public function isUserPassTheMarketplaceRule($user)
    {
        $isUserPass = false;
        $progressService = new ProgressService();
        $profile = $progressService->getUserProfilePageProgress($user);
        $package = $progressService->getUserPackagePageProgress($user);
        $geography = $progressService->getUserGeographyPageProgress($user);

        if ($profile == 100 && $package == 100 && $geography == 100) {
            $isUserPass = true;
        }

        return $isUserPass;
    }

    public function isActiveInMarketplace($user)
    {
        return $this->isUserActive($user) && $this->isUserPassTheMarketplaceRule($user);
    }
}
