<?php

namespace App\Http\Resources\Contact;

use Illuminate\Http\Resources\Json\JsonResource;

class ContactResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'userId' => $this->user_id,
            'connectionUserId' => $this->connection_user_id,
            'lastMessage_time' => $this->last_message_time,
            'lastMessage' => $this->last_message,
            'newMessageCount' => $this->new_message_count,
            'status' => $this->status,
        ];
    }
}
