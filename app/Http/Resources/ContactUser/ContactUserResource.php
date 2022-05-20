<?php

namespace App\Http\Resources\ContactUser;

use App\Entities\Booking;
use App\Entities\User;
use App\Services\Media\MediaService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class ContactUserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        $mediaService = new MediaService();
        $bookedItems = [];
        if ($this->contactAbleUserId && Auth::user()) {

            $bookedItems = Booking::with('order')->where('package_buyer_user_id', '=', $this->contactAbleUserId)
                ->where('package_owner_user_id', '=', Auth::user()->id)
//                ->where('status', '=', "Accepted")
                ->orderBy('updated_at', 'desc')
                ->get();
        }

        $allData = [
            'id' => $this->id,
            "categoryName" => $this->categoryName,
            "firstName" => $this->firstName,
            "lastName" => $this->lastName,
            "email" => $this->email,
            "status" => $this->status,
            "comment" => $this->comment,
            "receiverUserId" => $this->receiverUserId,
            "contactAbleUserId" => $this->contactAbleUserId,
            "contactAbleUser" => $this->contactAbleUserId,
            "lastActiveAt" => $this->lastActiveAt ?? "",
            "created_at" => $this->created_at->diffForHumans(),
        ];
        if ($this->contactAbleUserId){
            $contactAbleUser = User::find($this->contactAbleUserId);
            if($contactAbleUser){
                $allData['profileUrl'] = $mediaService->getImages($contactAbleUser);
            }
        }
        foreach ($bookedItems as $item){
            if ($this->contactAbleUserId == $item['package_buyer_user_id']){
                $allData['bookedItems'] = $bookedItems;
            }
            $allData['status'] = $item['status'];
        }
        return $allData;
    }
}
