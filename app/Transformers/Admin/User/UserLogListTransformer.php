<?php


namespace App\Transformers\Admin\User;

use App\Entities\User;
use App\Entities\UserLog;
use App\Services\StorageService;
use League\Fractal;

class UserLogListTransformer extends Fractal\TransformerAbstract
{
    private $storageService;

    public function __construct()
    {
        $this->storageService = new StorageService();
    }

    public function transform(UserLog $item)
    {
        $id = $item->id;
        $userId = $item->user_id;
        $fullName = $item->getFullName();
        $statusText = $item->getStatusText();
        $activityStatusReason = $item->activity_status_reason ?? '';
        $firstName =  $item->first_name;
        $lastName =  $item->last_name;
        $email =  $item->email;
        $date = $item->getCreatedDate();
        $phoneNumber = $item->phone;
        $image = $item->getImage();

        return [
            'id' => $id,
            'userId' => $userId,
            'date' => $date,
            'image' => $image,
            'email' => $email,
            'phoneNumber' => $phoneNumber,
            'fullName' => $fullName,
            'status'=> $statusText,
            'activityStatusReason'=> $activityStatusReason,
        ];
    }
}
