<?php


namespace App\Services;


class ProfileService
{
    public function getPhoneNumber($profile){
        $phoneNumber = '';
        if($profile){
            $phoneCode = '';
            if($profile->mobile_code){
                if($profile->mobile_code == 'DK'){
                    $phoneCode = '+45';
                } elseif ($profile->mobile_code == 'US'){
                    $phoneCode = '+1';
                }
            }
            if($profile->mobile_no){
                $phoneNumber = $phoneCode.'-'.$profile->mobile_no;
            }
        }
        return $phoneNumber;
    }

    public function getImage($profile)
    {
        $storageService = new StorageService();
        return $storageService->hasImage($profile->image) ? $profile->image : null;
    }
}
