<?php

namespace App\Http\Resources\Admin\User;

use App\Services\Media\MediaService;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $mediaService = new MediaService();

        $profile = $this->profile;
        $fullName = $this->first_name.' '. $this->last_name;
        $id = $this->id;
        $roles = $this->roles;
        $roleText = implode(", ", $roles->pluck('display_name')->toArray());
        $statusText = $this->activityStatus->display_text ?? '';
        if ($this->trashed()) {
            $statusText = 'Deleted';
        }
        $packageCount = $this->packages()->count();
        $mediaCount = $this->galleries()->count();
        $activityStatus = $this->activityStatus;
        $starStatus = $this->starStatus;
        $activityStatusReason = $this->activity_status_reason ?? '';
        $activityStatusId = $this->activity_status_id ?? '';
        $starStatusId = $this->star_status_id ?? '';
        $firstName =  $this->first_name;
        $lastName =  $this->last_name;
        $email =  $this->email;
        $ranking =  $this->ranking;
        $badgeId =  $this->badge_id;
        $date = date('d-m-y', strtotime($this->created_at));

        $image = null;
        $phoneCode = '';
        $phoneNumber = '';
        $phoneText = '';

        if($profile){
            $phoneCode = $profile->mobile_code ?? '';
            $phoneNumber = $profile->mobile_no ?? '';
            $images = $mediaService->getImages($this);
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
