<?php

namespace App\Entities;

use App\Services\ProfileService;
use App\Services\StorageService;
use Illuminate\Database\Eloquent\Model;

class UserLog extends Model
{
    protected $table = 'user_logs';

    public function activityStatus()
    {
        return $this->belongsTo(ActivityStatus::class, 'activity_status_id');
    }

    public function starStatus()
    {
        return $this->belongsTo(StarStatus::class, 'star_status_id');
    }

    public function getStatusText()
    {
        $statusText = $this->activityStatus->display_text ?? '';
        if ($this->deleted_at) {
            $statusText = 'Deleted';
        }
        return $statusText;
    }

    public function getCreatedDate()
    {
        return date('d-m-y', strtotime($this->created_at));
    }

    public function getFullName()
    {
        return $this->first_name.' '. $this->last_name;
    }


    public function getImage()
    {
        $storageService = new StorageService();
        return $storageService->hasImage($this->image) ? $this->image : null;
    }

    public static function createByUser($user)
    {
        if($user){
            $profile = $user->profile;
            $profileService = new ProfileService();

            $phoneNumber = $profileService->getPhoneNumber($profile);
            $image = $profileService->getImage($profile);

            $userLog = new self();
            $userLog->user_id = $user->id;
            $userLog->first_name = $user->first_name;
            $userLog->last_name = $user->last_name;
            $userLog->email = $user->email;
            $userLog->user_name = $user->user_name;
            $userLog->password = $user->password;
            $userLog->phone = $phoneNumber;
            $userLog->image = $image;
            $userLog->verified = $user->verified;
            $userLog->agree_to_terms = $user->agree_to_terms;
            $userLog->activity_status_id = $user->activity_status_id;
            $userLog->activity_status_reason = $user->activity_status_reason;
            $userLog->star_status_id = $user->star_status_id;
            $userLog->deleted_at = $user->deleted_at;
            $userLog->save();
        }
    }


}
