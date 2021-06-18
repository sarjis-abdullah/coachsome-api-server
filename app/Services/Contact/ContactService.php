<?php


namespace App\Services\Contact;


use App\Entities\Contact;
use App\Entities\User;
use App\Http\Resources\Category\SportCategoryResource;
use App\Http\Resources\Language\LanguageResource;
use App\Http\Resources\Tag\SportTagResource;
use App\Services\Media\MediaService;
use App\Services\StorageService;

class ContactService
{

    public function getContact($user)
    {
        $storageService = new StorageService();
        $mediaService = new MediaService();


        $contactUserIdList = Contact::where('user_id', $user->id)
            ->pluck('connection_user_id')
            ->toArray();


        $users = User::join('contacts', 'users.id', '=', 'contacts.connection_user_id')
            ->whereIn('users.id', $contactUserIdList)
            ->where('contacts.user_id', $user->id)
            ->orderBy('contacts.last_message_time', 'DESC')
            ->select('users.*')
            ->get()->map(function ($item) use ($storageService, $user, $mediaService) {
                $firstName = '';
                $lastName = '';
                $avatarImage = null;
                $profileName = '';
                $aboutText = '';
                $profile = null;

                $newMessageCount = 0;
                $lastMessageTime = null;
                $lastMessage = null;

                $contact = Contact::where('user_id', $user->id)->where('connection_user_id', $item->id)->first();

                if ($item) {
                    $userId = $item->id;
                    $firstName = $item->first_name ?? '';
                    $lastName = $item->last_name ?? '';
                    $profile = $item->profile;
                    $avatarName = strtoupper(mb_substr($firstName, 0, 1)) . strtoupper(mb_substr($lastName, 0, 1));
                    $email = $item->email;
                    $profileName = $item->profileName();
                }

                if ($profile) {
                    $aboutText = $profile->about_me;
                }

                $images = $mediaService->getImages($item);
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
                    'languages' => LanguageResource::collection($item->languages),
                    'categories' => SportCategoryResource::collection($item->sportCategories),
                    'tags' => SportTagResource::collection($item->sportTags),
                    'newMessageCount' => $newMessageCount,
                    'lastMessageTime' => $lastMessageTime,
                    'lastMessage' => $lastMessage,
                ];

            });

        return $users;
    }

}
