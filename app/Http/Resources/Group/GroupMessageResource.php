<?php

namespace App\Http\Resources\Group;

use App\Data\MessageData;
use App\Entities\User;
use App\Http\Resources\User\UserResource;
use App\Services\MinioService;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class GroupMessageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $senderUser = User::find($this->sender_user_id);

        $content = json_decode($this->content);

        if($this->type == 'structure' && $content->key == 'attachment'){
            $minioService = new MinioService(); 
            $content->url = $minioService->getAttachmentUrl($content->url);
        }


        return [
            'id' => $this->id,
            'type' => $this->type,
            'scope' => MessageData::SCOPE_GROUP,
            'me' => $this->sender_user_id == Auth::id(),
            'content' => $content,
            'createdAt' => $this->date_time_iso,
            'groupId' => $this->group_id,
            'categoryId' => $this->message_category_id,
            'senderUserId' => $this->sender_user_id,
            'senderUser' => $senderUser ? new UserResource($senderUser) : []
        ];
    }
}
