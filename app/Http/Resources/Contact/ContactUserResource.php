<?php

namespace App\Http\Resources\Contact;

use App\Entities\Contact;
use App\Entities\User;
use App\Http\Resources\Category\SportCategoryResource;
use App\Http\Resources\Language\LanguageResource;
use App\Http\Resources\Tag\SportTagResource;
use App\Services\Media\MediaService;
use Illuminate\Http\Resources\Json\JsonResource;

class ContactUserResource extends JsonResource
{

    private $user, $mediaService;

    public function __construct($resource, $user)
    {
        $this->user = $user;
        $this->mediaService = new MediaService();
        parent::__construct($resource);
    }

    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $firstName = '';
        $lastName = '';
        $avatarImage = null;
        $profileName = '';
        $profile = null;
        $aboutText = '';
        $newMessageCount = 0;
        $lastMessageTime = null;
        $lastMessage = null;

        $contact = Contact::where('user_id', $this->user->id)->where('connection_user_id', $this->id)->first();

        $userId = $this->id;
        $firstName = $this->first_name ?? '';
        $lastName = $this->last_name ?? '';
        $profile = $this->profile;
        $avatarName = strtoupper(mb_substr($firstName, 0, 1)) . strtoupper(mb_substr($lastName, 0, 1));
        $email = $this->email;
        $profileName = $this->profileName();


        if ($profile) {
            $aboutText = $profile->about_me;
        }

        $images = $this->mediaService->getImages($this);
        if ($images['square']) {
            $avatarImage = $images['square'];
        } else {
            $avatarImage = $images['old'];
        }

        if ($contact) {
            $newMessageCount = $contact->new_message_count;
            $lastMessageTime = $contact->last_message_time;
            $lastMessage = $contact->last_message;
        }

        return [
            'id' => $userId,
            'firstName' => $firstName,
            'lastName' => $lastName,
            'email' => $email,
            'fullName' => $profileName,
            'title' => '',
            'avatarImage' => $avatarImage,
            'avatarName' => $avatarName,
            'aboutText' => $aboutText,
            'languages' => LanguageResource::collection($this->languages),
            'categories' => SportCategoryResource::collection($this->sportCategories),
            'tags' => SportTagResource::collection($this->sportTags),
            'newMessageCount' => $newMessageCount,
            'lastMessageTime' => $lastMessageTime,
            'lastMessage' => $lastMessage,
            'isOnline' => $this->is_online,
            'status' => $contact->status
        ];

    }
}
