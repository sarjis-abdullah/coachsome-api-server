<?php

namespace App\Http\Controllers\Api\V1\General;

use App\Data\StatusCode;
use App\Entities\Booking;
use App\Entities\Contact;
use App\Entities\Message;
use App\Entities\PendingNotification;
use App\Entities\User;
use App\Http\Controllers\Controller;
use App\Jobs\NewMessageInformer;
use App\Notifications\NewTextMessage;
use App\Services\BookingService;
use App\Services\ContactService;
use App\Services\MessageFormatterService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MessageController extends Controller
{

    public function index(Request $request)
    {
        try {
            $messages = [];
            $newMessages = [];

            $authUser = Auth::user();
            $connectedUser = User::find($request->query('userId'));

            if (!$connectedUser) {
                throw new \Exception('Selected user not found');
            }

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
            })->where('status', 'Initial')->get();

            // New messages
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
                'type' => 'required'
            ]);
            $receiverUserId = $request['receiverUserId'];
            $messageContent = $request['content'];
            $createdAt = $request['created_at'];
            $type = $request['type'];

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
            $message->receiver_user_id = $receiverUser->id;
            $message->text_content = $type == 'text' ? $messageContent : null;
            $message->type = $type;
            $message->structure_content = $type == 'structure' ? json_encode($messageContent) : null;
            $message->date_time = Carbon::now();
            $message->date_time_iso = $createdAt;
            $message->save();

            // Disconnected user pending for a mail notification
            if (!$receiverUser->is_online) {
                $job = (new NewMessageInformer($receiverUser,$message))->delay(now()->addMinutes(1));
                $jobId = $this->dispatch(
                    $job
                );
                $pendingNotification = new PendingNotification();
                $pendingNotification->user_id = $receiverUser->id;
                $pendingNotification->job_id = $jobId;
                $pendingNotification->save();
            }


            $contactService->updateLastMessageAndTime($senderUser, $receiverUser, $message);

            return response()->json([
                'message' => 'Successfully receive a message'
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
}
