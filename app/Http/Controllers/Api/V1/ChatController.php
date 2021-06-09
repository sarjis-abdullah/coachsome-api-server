<?php

namespace App\Http\Controllers\Api\V1;

use App\Data\StatusCode;
use App\Entities\Booking;
use App\Entities\BookingTime;
use App\Entities\Contact;
use App\Entities\Message;
use App\Entities\User;
use App\Http\Controllers\Controller;
use App\Http\Resources\Category\SportCategoryResource;
use App\Http\Resources\Language\LanguageResource;
use App\Http\Resources\Tag\SportTagResource;
use App\Services\BookingService;
use App\Services\ContactService;
use App\Services\MessageFormatterService;
use App\Services\OrderService;
use App\Services\QuickpayClientService;
use App\Services\StorageService;
use App\Services\TransformerService;
use App\Services\TranslationService;
use App\ValueObjects\Message\BigText;
use App\ValueObjects\Message\BuyPackage;
use App\ValueObjects\Message\PackageBooking;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use QuickPay\QuickPay;

class ChatController extends Controller
{
    public function index(Request $request)
    {
        $authUser = Auth::user();
        $users = $this->getUserContact($authUser);
        return response()->json([
            'users' => $users
        ]);
    }

    public function getMessage(Request $request)
    {
        try {
            $newMessages = [];
            $oldMessages = null;

            $selectedUserId = $request->query('selectedUserId');

            $selectedUser = User::find($selectedUserId);
            $authUser = Auth::user();

            if (!$selectedUser) {
                throw new \Exception('Selected user not found');
            }

            $contactService = new ContactService();
            $messageFormatterService = new MessageFormatterService();
            $bookingService = new BookingService();

            // Old messages
            $oldMessages = Message::where(function ($q) use ($selectedUser, $authUser) {
                $q->where('sender_user_id', $selectedUser->id);
                $q->where('receiver_user_id', $authUser->id);
            })->orWhere(function ($q) use ($selectedUser, $authUser) {
                $q->where('sender_user_id', $authUser->id);
                $q->where('receiver_user_id', $selectedUser->id);
            })->get()->map(function ($item) use ($messageFormatterService) {
                return $messageFormatterService->doFormat($item);
            });


            // Initial bookings
            $bookings = Booking::where(function ($q) use ($selectedUser, $authUser) {
                $q->where('package_owner_user_id', $selectedUser->id);
                $q->where('package_buyer_user_id', $authUser->id);
            })->where('status', 'Initial')->get();

            // New messages
            if ($bookings->count() > 0) {
                $initialBookingMessages = $bookingService->checkPaymentStatusOfInitialBookings($bookings);
                $newMessages = $initialBookingMessages["newMessages"];
                $contactService->resetContactNewMessageCount($authUser, $selectedUser);
            }


            return response()->json([
                'messages' => $oldMessages,
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

    public function storeMessage(Request $request)
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

            $message = new Message();
            $message->sender_user_id = $senderUser->id;
            $message->receiver_user_id = $receiverUser->id;
            $message->text_content = $type == 'text' ? $messageContent : null;
            $message->type = $type;
            $message->structure_content = $type == 'structure' ? json_encode($messageContent) : null;
            $message->date_time = Carbon::now();
            $message->date_time_iso = $createdAt;

            $message->save();

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

    public function getContact()
    {
        $authUser = Auth::user();
        $users = $this->getUserContact($authUser);
        return response()->json([
            'users' => $users
        ]);
    }

    private function getUserContact($user)
    {
        $storageService = new StorageService();

        $contactUserIdList = Contact::where('user_id', $user->id)
            ->pluck('connection_user_id')
            ->toArray();


        $users = User::join('contacts', 'users.id', '=', 'contacts.connection_user_id')
            ->whereIn('users.id', $contactUserIdList)
            ->where('contacts.user_id', $user->id)
            ->orderBy('contacts.last_message_time', 'DESC')
            ->select('users.*')
            ->get()->map(function ($item) use ($storageService, $user) {
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
                    $avatarImage = $storageService->hasImage($profile->image) ? $profile->image : null;
                    $aboutText = $profile->about_me;
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

    public function getTotalNewMessageCount()
    {
        $authUser = Auth::user();

        $totalNewMessageCount = Contact::where('user_id', $authUser->id)
            ->sum('new_message_count');

        return response()->json([
            'totalNewMessageCount' => $totalNewMessageCount
        ], StatusCode::HTTP_OK);

    }

}
