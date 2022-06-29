<?php

namespace App\Http\Controllers\Api\V1\General;

use App\Data\MessageData;
use App\Data\OrderStatus;
use App\Data\StatusCode;
use App\Entities\Booking;
use App\Entities\Contact;
use App\Entities\ContactUser;
use App\Entities\Message;
use App\Entities\MessageCategory;
use App\Entities\PendingNotification;
use App\Entities\User;
use App\Events\CreateNewContactUserEvent;
use App\Http\Controllers\Controller;
use App\Jobs\NewMessageInformer;
use App\Notifications\NewTextMessage;
use App\Services\BookingService;
use App\Services\ContactService;
use App\Services\MessageFormatterService;
use App\ValueObjects\Message\Attachment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use MessageFormatter;

class MessageController extends Controller
{
    public function index(Request $request)
    {
        try {
            $messages = [];
            $newMessages = [];

            $authUser = Auth::user();
            $contact = Contact::find($request['contactId']);

            if (!$contact) {
                throw new \Exception('Contact information is not found');
            }

            $connectedUser = User::find($contact->connection_user_id);

            $contactService = new ContactService();
            $messageFormatterService = new MessageFormatterService();
            $bookingService = new BookingService();

            // Existing messages
            $messages = Message::where(function ($q) use ($connectedUser, $authUser) {
                $q->where('sender_user_id', $connectedUser->id);
                $q->where('receiver_user_id', $authUser->id);
            })->orWhere(function ($q) use ($connectedUser, $authUser) {
                $q->where('sender_user_id', $authUser->id);
                $q->where('receiver_user_id', $connectedUser->id);
            })->get()->map(function ($item) use ($messageFormatterService) {
                return $messageFormatterService->doFormat($item);
            });

            // Initial bookings
            $bookings = Booking::where(function ($q) use ($connectedUser, $authUser) {
                $q->where('package_owner_user_id', $connectedUser->id);
                $q->where('package_buyer_user_id', $authUser->id);
            })->where('status', OrderStatus::INITIAL)->get();

            // New messages
            // Initial status bookings needs to send message to the client
            if ($bookings->count() > 0) {
                $initialBookingMessages = $bookingService->checkPaymentStatusOfInitialBookings($bookings);
                $newMessages = $initialBookingMessages["newMessages"];
            }

            // Reset new message number
            $contactService->resetContactNewMessageCount($authUser, $connectedUser);

            return response()->json([
                'messages' => $messages,
                'newMessages' => $newMessages,
            ], StatusCode::HTTP_OK);

        } catch (\Exception $e) {
            if ($e instanceof ValidationException) {
                return response()->json(
                    $e->validator->errors()->first(),
                    StatusCode::HTTP_UNPROCESSABLE_ENTITY
                );
            }

            return response()->json([
                'message' => 'Line: ' . $e->getLine() . ' Error: ' . $e->getMessage()
            ], StatusCode::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'receiverUserId' => 'required',
                'content' => 'required',
                'type' => 'nullable',
                'categoryId' => 'nullable'

            ]);
            $receiverUserId = $request['receiverUserId'];
            $messageContent = $request['content'];
            $createdAt = $request['createdAt'];
            $type = $request['type'];
            $categoryId = $request['categoryId'];

            if($categoryId){
                $messageCategory = MessageCategory::find($categoryId);
                if(!$messageCategory){
                    throw new \Exception('Message category do not found');
                }
            } else {
                $categoryId = MessageData::CATEGORY_ID_TEXT;
            }


            $receiverUser = User::find($receiverUserId);
            if (!$receiverUser) {
                throw new \Exception('Receiver user not found');
            }
            $senderUser = Auth::user();
            $contactService = new ContactService();

            // Create contact
            if ($senderUser->id != $receiverUser->id) {
                $contactService->create($senderUser, $receiverUser);
            }

            $message = new Message();
            $message->sender_user_id = $senderUser->id;
            $message->message_category_id = $categoryId;
            $message->receiver_user_id = $receiverUser->id;
            $message->text_content = $type == 'text' ? $messageContent : null;
            $message->type = $type;
            $message->structure_content = $type == 'structure' ? json_encode($messageContent) : null;
            $message->date_time = Carbon::now();
            $message->date_time_iso = $createdAt;
            $message->save();

            // Disconnected user pending for a mail notification
            if (!$receiverUser->is_online) {
                $job = (new NewMessageInformer($receiverUser,$message))->delay(now()->addMinutes(5));
                $jobId = $this->dispatch(
                    $job
                );
                $pendingNotification = new PendingNotification();
                $pendingNotification->user_id = $receiverUser->id;
                $pendingNotification->job_id = $jobId;
                $pendingNotification->save();
            }


            $contactService->updateLastMessageAndTime($senderUser, $receiverUser, $message);
            event(new CreateNewContactUserEvent([
                'receiverUserId' => $receiverUserId,
                'contactAbleUserId' => $senderUser->id,
                'email' => $senderUser->email,
                'firstName' => $senderUser['first_name'],
                'lastName' => $senderUser['last_name'],
                'status' => ContactUser::STATUS_ACTIVE,
                'comment' => "Created while sending message",
            ]));
            return response()->json([
                'message' => 'Successfully receive a message',
                'data' => $message,
            ], StatusCode::HTTP_OK);

        } catch (\Exception $e) {
            if ($e instanceof ValidationException) {
                return response()->json(
                    $e->validator->errors()->first(),
                    StatusCode::HTTP_UNPROCESSABLE_ENTITY
                );
            }

            return response()->json([
                'message' => $e->getMessage()
            ], StatusCode::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function getNewCount()
    {
        $authUser = Auth::user();

        $totalNewMessageCount = Contact::where('user_id', $authUser->id)
            ->sum('new_message_count');

        return response()->json([
            'totalNewMessageCount' => $totalNewMessageCount
        ], StatusCode::HTTP_OK);

    }

    public function storeAttachment(Request $request){
        try {
            $request->validate([
                'receiverUserId' => 'required',
                'type' => 'nullable',
                'categoryId' => 'nullable',
                'file' => 'required|mimes:jpg,png,gif,svg|max:5000'

            ]);

            $name = $request->file('file')->store(
                '', 'minio'
            );
            // $name = Storage::disk('minio')->put('contents', ($request->file('file')));

            $attachment = $name;

            $messageContent = new Attachment([
                'url' => $attachment
            ]);


            $receiverUserId = $request['receiverUserId'];
            $createdAt = $request['createdAt'];
            $type = $request['type'];
            $categoryId = $request['categoryId'];

            if($categoryId){
                $messageCategory = MessageCategory::find($categoryId);
                if(!$messageCategory){
                    throw new \Exception('Message category do not found');
                }
            } else {
                $categoryId = MessageData::CATEGORY_ID_TEXT;
            }


            $receiverUser = User::find($receiverUserId);
            if (!$receiverUser) {
                throw new \Exception('Receiver user not found');
            }
            $senderUser = Auth::user();
            $contactService = new ContactService();

            // Create contact
            if ($senderUser->id != $receiverUser->id) {
                $contactService->create($senderUser, $receiverUser);
            }

            $message = new Message();
            $message->sender_user_id = $senderUser->id;
            $message->message_category_id = $categoryId;
            $message->receiver_user_id = $receiverUser->id;
            $message->type = $type;
            $message->structure_content = $type == 'structure' ? $messageContent->toJson() : null;
            $message->date_time = Carbon::now();
            $message->date_time_iso = $createdAt;
            $message->save();

            // Disconnected user pending for a mail notification
            if (!$receiverUser->is_online) {
                $job = (new NewMessageInformer($receiverUser,$message))->delay(now()->addMinutes(5));
                $jobId = $this->dispatch(
                    $job
                );
                $pendingNotification = new PendingNotification();
                $pendingNotification->user_id = $receiverUser->id;
                $pendingNotification->job_id = $jobId;
                $pendingNotification->save();
            }


            $contactService->updateLastMessageAndTime($senderUser, $receiverUser, $message);
            $messageFormatService = new MessageFormatterService();
            $messageData = $messageFormatService->doFormat($message);

            return response()->json([
                'message' => $messageData
            ], StatusCode::HTTP_OK);

        } catch (\Exception $e) {
            if ($e instanceof ValidationException) {
                return response()->json(
                    $e->validator->errors()->first(),
                    StatusCode::HTTP_UNPROCESSABLE_ENTITY
                );
            }

            return response()->json([
                'message' => $e->getMessage()
            ], StatusCode::HTTP_UNPROCESSABLE_ENTITY);
        }

    }
}
