<?php

namespace App\Http\Resources\Contact;

use App\Data\ContactData;
use App\Entities\Group;
use App\Entities\GroupMessage;
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
        $lastMessageTimeIso = "";

        if (ContactData::CATEGORY_ID_PRIVATE == $this->contact_category_id) {
            $user = User::find($this->connection_user_id);
            $users = [new UserResource($user)];
            if ($user && $user['first_name'] && $user['last_name'])
                $contactName = $user->first_name . ' ' . $user->last_name;
            if ($this->last_message) {
                $message = json_decode($this->last_message);
                $lastMessage = $message->text_content ?? '';
                $lastMessageTimeIso = $message->date_time_iso ?? "";
            }
        }

        if (ContactData::CATEGORY_ID_GROUP == $this->contact_category_id) {
            $group = Group::find($this->group_id);
            if ($group) {
                $groupMessage = GroupMessage::where('group_id', $group->id)->orderBy('created_at', 'DESC')->first();
                $groupName = $group->name;
                $lastMessage = $group->message;
                if($groupMessage){
                    $lastMessageTimeIso = $groupMessage->date_time_iso;
                }
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
            'lastMessageTime' => $lastMessageTimeIso,
            'lastMessage' => $lastMessage,
            'newMessageCount' => $this->new_message_count,
            'status' => $this->status,
            'users' => $users,
            'group' => $group ? new  GroupResource($group) : null
        ];
    }
}
