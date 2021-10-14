<?php


namespace App\Services\Contact;

use App\Entities\ChatSetting;
use App\Entities\Contact;
use App\Entities\Message;
use App\Entities\User;
use App\Http\Resources\Category\SportCategoryResource;
use App\Http\Resources\Chat\ChatSettingResource;
use App\Http\Resources\Language\LanguageResource;
use App\Http\Resources\Tag\SportTagResource;
use App\Services\Media\MediaService;
use App\Services\StorageService;
use Carbon\Carbon;

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
                $chatSettings = null;

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
                    'isOnline' => $item->is_online,
                    'status' =>  $contact->status
                ];

            });

        return $users;
    }


    public function create(User $firstUser, User $secondUser)
    {
        if ($firstUser->id != $secondUser->id) {
            $firstUserExistedContact = Contact::where('user_id', $firstUser->id)
                ->where('connection_user_id', $secondUser->id)
                ->first();

            $secondUserExistedContact = Contact::where('user_Id', $secondUser->id)
                ->where('connection_user_id', $firstUser->id)
                ->first();

            if (!$firstUserExistedContact) {
                $contact = new Contact();
                $contact->user_id = $firstUser->id;
                $contact->connection_user_id = $secondUser->id;
                $contact->last_message_time = Carbon::now();
                $contact->status = 'Initial';
                $contact->save();
            }

            if (!$secondUserExistedContact) {
                $contact = new Contact();
                $contact->user_id = $secondUser->id;
                $contact->connection_user_id = $firstUser->id;
                $contact->last_message_time = Carbon::now();
                $contact->status = 'Initial';
                $contact->save();
            }
        }
    }

    /**
     * @param User $sender
     * @param User $receiver
     * @param Message|null $message
     * @param String $timeStamp
     */
    public function updateLastMessageAndTime(User $sender, User $receiver, Message $message = null, $timeStamp = '')
    {
        $senderExistedContact = Contact::where('user_id', $sender->id)
            ->where('connection_user_id', $receiver->id)
            ->first();

        $receiverExistedContact = Contact::where('user_Id', $receiver->id)
            ->where('connection_user_id', $sender->id)
            ->first();

        if ($senderExistedContact) {
            if ($timeStamp) {
                $senderExistedContact->last_message_time = $timeStamp;
            } else {
                $senderExistedContact->last_message_time = Carbon::now();
            }
            if ($message) {
                $senderExistedContact->last_message = $message->toJson();
            }
            $senderExistedContact->save();
        }
        if ($receiverExistedContact) {
            if ($timeStamp) {
                $receiverExistedContact->last_message_time = $timeStamp;
            } else {
                $receiverExistedContact->last_message_time = Carbon::now();
            }
            if ($message) {
                $receiverExistedContact->last_message = $message;
            }
            $receiverExistedContact->new_message_count = ++$receiverExistedContact->new_message_count;
            $receiverExistedContact->save();
        }
    }

    public function resetContactNewMessageCount(User $contactOwnerUser,User $connectionUser)
    {
        $contact = Contact::where('user_id', $contactOwnerUser->id)
            ->where('connection_user_id', $connectionUser->id)
            ->first();
        if ($contact) {
            $contact->new_message_count = 0;
            $contact->save();
        }
    }

}
