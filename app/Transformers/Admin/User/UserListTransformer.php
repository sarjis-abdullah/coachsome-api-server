<?php


namespace App\Transformers\Admin\User;

use App\Entities\User;
use App\Services\Media\MediaService;
use App\Services\StorageService;
use League\Fractal;

class UserListTransformer extends Fractal\TransformerAbstract
{
    private $storageService;
    private $mediaService;

    public function __construct()
    {
        $this->storageService = new StorageService();
        $this->mediaService = new MediaService();
    }

    public function transform(User $item)
    {
        $profile = $item->profile;
        $fullName = $item->first_name.' '. $item->last_name;
        $id = $item->id;
        $roles = $item->roles;
        $roleText = implode(", ", $roles->pluck('display_name')->toArray());
        $statusText = $item->activityStatus->display_text ?? '';
        if ($item->trashed()) {
            $statusText = 'Deleted';
        }
        $packageCount = $item->packages()->count();
        $mediaCount = $item->galleries()->count();
        $activityStatus = $item->activityStatus;
        $starStatus = $item->starStatus;
        $activityStatusReason = $item->activity_status_reason ?? '';
        $activityStatusId = $item->activity_status_id ?? '';
        $starStatusId = $item->star_status_id ?? '';
        $firstName =  $item->first_name;
        $lastName =  $item->last_name;
        $email =  $item->email;
        $ranking =  $item->ranking;
        $badgeId =  $item->badge_id;
        $date = date('d-m-y', strtotime($item->created_at));

        $image = null;
        $phoneCode = '';
        $phoneNumber = '';
        $phoneText = '';

        if($profile){
            $phoneCode = $profile->mobile_code ?? '';
            $phoneNumber = $profile->mobile_no ?? '';
            $images = $this->mediaService->getImages($item);
            if($images['square']){
                $image =$images['square'];
            } else {
                $image =$images['old'];
            }
            if($phoneCode && $phoneNumber){
                $code= "";
                if($phoneCode == 'DK'){
                    $code = '+45';
                } elseif ($phoneCode == 'US'){
                    $code = '+1';
                }
                $phoneText = $code.'-'.$phoneNumber;
            }
        }

        return [
            'id' => $id,
            'date' => $date,
            'image' => $image,
            'email' => $email,
            'phoneCode' => $phoneCode,
            'phoneNumber' => $phoneNumber,
            'phoneText' => $phoneText,
            'firstName' => $firstName,
            'lastName' => $lastName,
            'roles' => $roles,
            'type' => $roleText,
            'fullName' => $fullName,
            'status'=> $statusText,
            'ranking'=> $ranking,
            'badgeId'=> $badgeId,
            'booking'=> 0,
            'declined'=> 0,
            'packageCount'=> $packageCount,
            'mediaCount'=> $mediaCount,
            'activityStatusReason'=> $activityStatusReason,
            'activityStatusId'=> $activityStatusId,
            'starStatusId'=> $starStatusId,
            'activityStatus'=> $activityStatus,
            'starStatus'=> $starStatus,
        ];
    }
}
