<?php


namespace App\Services;

use App\Entities\Contact;
use App\Entities\Message;
use App\Entities\User;
use Carbon\Carbon;


class ContactService
{
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
