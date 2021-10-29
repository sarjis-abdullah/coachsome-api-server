<?php

namespace App\Http\Resources\Contact;

use App\Data\ContactData;
use App\Entities\Group;
use App\Entities\User;
use App\Http\Resources\Group\GroupResource;
use App\Http\Resources\User\UserResource;
use Illuminate\Http\Resources\Json\JsonResource;

class ContactResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $users = [];
        $groupName = "";
        $contactName = "";
        $group = null;
        $lastMessage = "";

        if (ContactData::CATEGORY_ID_PRIVATE == $this->contact_category_id) {
            $user = User::find($this->connection_user_id);
            $users = [new UserResource($user)];
            $contactName = $user->first_name . ' ' . $user->last_name;
            if ($this->last_message) {
                $message = json_decode($this->last_message);
                $lastMessage =  $message->text_content ?? '';
            }
        }

        if (ContactData::CATEGORY_ID_GROUP == $this->contact_category_id) {
            $group = Group::find($this->group_id);
            if ($group) {
                $groupName = $group->name;
                $lastMessage =$group->message;
                $users = [];
            }
        }

        return [
            'id' => $this->id,
            'userId' => $this->user_id,
            'groupId' => $this->group_id,
            'groupName' => $groupName,
            'contactName' => $contactName,
            'categoryId' => $this->contact_category_id,
            'connectionUserId' => $this->connection_user_id,
            'lastMessageTime' => $this->last_message_time,
            'lastMessage' => $lastMessage,
            'newMessageCount' => $this->new_message_count,
            'status' => $this->status,
            'users' => $users,
            'group' => $group ? new  GroupResource($group) : null
        ];
    }
}
