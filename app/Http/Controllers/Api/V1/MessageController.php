<?php

namespace App\Http\Controllers\Api\V1;

use App\Data\StatusCode;
use App\Entities\Contact;
use App\Entities\Message;
use App\Entities\User;
use App\Http\Controllers\Controller;
use App\Services\ContactService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class MessageController extends Controller
{

    public function store(Request $request)
    {
        try {
            $receiverUserId = $request->userId;
            $message = $request->message;

            $receiverUser = User::find($receiverUserId);
            $senderUser = Auth::user();
            $contactService = new ContactService();

            if (!$message) {
                throw new Exception('Sorry, message is not found.');
            }

            if (!$receiverUser || !$senderUser) {
                throw new Exception('Sorry, user is not found');
            }


            if ($senderUser->id == $receiverUser->id) {
                throw new Exception('You can not send a message to yourself.');
            }

            // Create contact
            if ($senderUser->id != $receiverUser->id) {
                $contactService->create($senderUser, $receiverUser);
            }

            // New message
            $mMessage = new Message();
            $mMessage->type = 'text';
            $mMessage->sender_user_id = $senderUser->id;
            $mMessage->receiver_user_id = $receiverUser->id;
            $mMessage->text_content = $message;
            $mMessage->date_time = Carbon::now();
            $mMessage->save();

            $contactService->updateLastMessageAndTime($senderUser, $receiverUser, $mMessage);

            return response()->json([
                'message' => 'Successfully created a message',
                'newMesssage' => $mMessage
            ], StatusCode::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], StatusCode::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
