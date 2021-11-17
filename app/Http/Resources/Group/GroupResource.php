<?php

namespace App\Http\Resources\Group;

use App\Services\Media\MediaService;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class GroupResource extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $mediaService = new MediaService();
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'createdUserId' => $this->created_user_id,
            'isAdmin' => $this->created_user_id == Auth::id(),
            'connectionUsersId' => $this->connection_users_id,
            'image' => $mediaService->hasGroupImage($this->image) ? $mediaService->getGroupImageUrl($this->image) : null,
            'createdAt' => $this->created_at,
        ];
    }
}
