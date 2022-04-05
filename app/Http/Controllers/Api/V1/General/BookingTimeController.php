<?php

namespace App\Http\Controllers\Api\V1\General;

use App\Data\MessageData;
use App\Data\SettingValue;
use App\Data\StatusCode;
use App\Entities\Booking;
use App\Entities\BookingLocation;
use App\Entities\BookingTime;
use App\Entities\Location;
use App\Entities\Message;
use App\Entities\NotificationSetting;
use App\Entities\User;
use App\Http\Controllers\Controller;
use App\Mail\AcceptedBookingRequest;
use App\Mail\AthletePackageConfirmation;
use App\Mail\DeclinedBookingRequest;
use App\Mail\PendingBookingRequest;
use App\Services\ContactService;
use App\Services\MessageFormatterService;
use App\Services\Mixpanel\MixpanelService;
use App\ValueObjects\Message\AcceptedBookingTime;
use App\ValueObjects\Message\DeclinedBookingTime;
use App\ValueObjects\Message\TimeBooking;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class BookingTimeController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $bookingId = $request->bookingId;
            $calenderDate = $request->date;
            $startTime = $request->startTime;
            $endTime = $request->endTime;
            $timeRange = $request->timeRange;
            $userId = $request->userId;
            $packageSession = $request->packageSession;
            $zip = $request->zip;
            $city = $request->city;
            $address = $request->address;
            $long = $request->long;
            $lat = $request->lat;
            $isSoldPackageChoosing = $request->isSoldPackageChoosing;
            $timeSlot = null;
            $booking = Booking::find($bookingId);
            $authUser = Auth::user();
            $packageOwnerUser = User::find($userId);
            $messageFormatterService = new MessageFormatterService();

            $t_key = "";

            if (!$packageOwnerUser) {
                throw new \Exception('User not found');
            }

            if (!$timeRange && !$isSoldPackageChoosing) {
                throw new \Exception('Choose a time range.');
            }

            if (!$calenderDate) {
                throw new \Exception('Select a date');
            }

            if (!$startTime || !$endTime) {
                throw new \Exception('Choose start and end time of your session');
            }

            if (!$booking) {
                throw new \Exception('Sorry! booking information not found.');
            }

            if (!$city || !$address || !$long || !$lat) {
                throw new \Exception('Location is not selected correctly');
            }

            // Find if there has any overlapping time point
            if (!$isSoldPackageChoosing) {
                $isOverlappingBookingTime = false;
                $timeSlot = [
                    'startTime' => $timeRange['startTime'],
                    'endTime' => $timeRange['endTime']
                ];
                $givenDate = Carbon::parse($calenderDate);
                $givenFormattedDate = $givenDate->format('Y-m-d');
                $availability = $packageOwnerUser->availabilities->where('week_no', $givenDate->weekOfYear)->first();

                if ($availability) {
                    $days = json_decode($availability->days, true);
                    $weekStartDate = Carbon::parse($availability->week_start_date);
                    $searchedDay = null;
                    foreach ($days as $index => $day) {
                        if ($givenFormattedDate == $weekStartDate->copy()->addDays($index)->format('Y-m-d')) {
                            $bookingTimes = BookingTime::where('requester_to_user_id', $userId)
                                ->where('calender_date', $givenFormattedDate)
                                ->where('status', 'Accepted')
                                ->get();

                            foreach ($bookingTimes as $bookingTime) {
                                $bookedStartTime = Carbon::now();
                                $bookedEndTime = Carbon::now();
                                $givenStartTime = Carbon::now();
                                $givenEndTime = Carbon::now();

                                $bookedStartTime->hour(explode(':', $bookingTime->start_time)[0]);
                                $bookedStartTime->minute(explode(':', $bookingTime->start_time)[1]);
                                $bookedStartTime->second('00');
                                $bookedEndTime->hour(explode(':', $bookingTime->end_time)[0]);
                                $bookedEndTime->minute(explode(':', $bookingTime->end_time)[1]);
                                $bookedEndTime->second('00');

                                $givenStartTime->hour(explode(':', $startTime)[0]);
                                $givenStartTime->minute(explode(':', $startTime)[1]);
                                $givenStartTime->second('00');
                                $givenEndTime->hour(explode(':', $endTime)[0]);
                                $givenEndTime->minute(explode(':', $endTime)[1]);
                                $givenEndTime->second('00');

                                // Left Point
                                if ($givenStartTime->format('H:i') < $bookedStartTime->format('H:i') &&
                                    $givenEndTime->format('H:i') > $bookedStartTime->format('H:i')) {
                                    $isOverlappingBookingTime = true;
                                    break;
                                }

                                // Right Point
                                if ($givenStartTime->format('H:i') < $bookedEndTime->format('H:i') &&
                                    $givenEndTime->format('H:i') > $bookedEndTime->format('H:i')) {
                                    $isOverlappingBookingTime = true;
                                    break;
                                }

                                // Inner Point
                                if ($givenStartTime->format('H:i') >= $bookedStartTime->format('H:i') &&
                                    $givenEndTime->format('H:i') <= $bookedEndTime->format('H:i')) {
                                    $isOverlappingBookingTime = true;
                                    break;
                                }

                                // Outer Point
                                if ($givenStartTime->format('H:i') > $bookedStartTime->format('H:i') &&
                                    $givenEndTime->format('H:i') < $bookedEndTime->format('H:i')) {
                                    $isOverlappingBookingTime = true;
                                    break;
                                }
                            }
                        }
                    }
                }

                if ($isOverlappingBookingTime) {
                    throw new \Exception('You can not do that. Because your time is overlapping on booked time.');
                }
            }

            $bookingTime = new BookingTime();
            $bookingTime->booking_id = $booking->id;
            $bookingTime->requester_user_id = $authUser->id;
            $bookingTime->requester_to_user_id = $packageOwnerUser->id;
            $bookingTime->calender_date = date('Y-m-d', strtotime($calenderDate));
            $bookingTime->requested_date = date('Y-m-d H:i:s');
            $bookingTime->time_slot = json_encode($timeSlot);
            $bookingTime->session_in_minute = $packageSession;
            $bookingTime->start_time = $startTime;
            $bookingTime->end_time = $endTime;
            $bookingTime->status = 'Pending';
            $bookingTime->save();

            $bookingLocation = new BookingLocation();
            $bookingLocation->user_id = $authUser->id;
            $bookingLocation->booking_time_id = $bookingTime->id;
            $bookingLocation->lat = $lat;
            $bookingLocation->long = $long;
            $bookingLocation->address = $address;
            $bookingLocation->zip = $zip;
            $bookingLocation->city = $city;
            $bookingLocation->save();

            // Send mail to requester to
            $requesterToUser = User::find($bookingTime->requester_to_user_id);
            if ($requesterToUser) {
                // Before sending email notification you have to check setting
                $requesterToUserNotificationSetting = NotificationSetting::where('user_id', $requesterToUser->id)->first();
                if($requesterToUserNotificationSetting &&
                    $requesterToUserNotificationSetting->booking_request == SettingValue::ID_EMAIL){
                    Mail::to($requesterToUser)->send(new PendingBookingRequest($bookingTime));
                }
            }

            $timeBookingMessage = new TimeBooking([
                'bookingTimeId' => $bookingTime->id,
                'date' => $calenderDate,
                'timeSlot' => $timeSlot,
                'startTime' => $startTime,
                'endTime' => $endTime,
                'bookingLocation' => $bookingLocation->toArray(),
                'packageSnapshot' => $booking->order ? json_decode($booking->order->package_snapshot) : null,
                'status' => 'Pending',
            ]);

            $message = new Message();
            $message->message_category_id = MessageData::CATEGORY_ID_BOOKING_TIME;
            $message->sender_user_id = $authUser->id;
            $message->receiver_user_id = $packageOwnerUser->id;
            $message->type = 'structure';
            $message->structure_content = $timeBookingMessage->toJson();
            $message->booking_time_id = $bookingTime->id;
            $message->date_time = Carbon::now();
            $message->date_time_iso = Carbon::now()->toISOString();
            $message->save();

            $contactService = new ContactService();
            $contactService->updateLastMessageAndTime($authUser, $packageOwnerUser, $message);

            // if successfully created a request
            if(!$t_key){
                $t_key = "chat_booking_time_successfully_created";
            }

            return response()->json([
                'toastMessage' => 'Successfully created',
                'newMessage' => $messageFormatterService->doFormat($message),
                "t_key" => $t_key
            ], StatusCode::HTTP_OK);

        } catch (\Exception $e) {
            return response()->json([
                'toastMessage' => 'Line: ' . $e->getLine() . ' ' . $e->getMessage()
            ], StatusCode::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    private function trackAcceptedSession($bookingTime)
    {
        $mixpanelService = new MixpanelService();
        $mp = $mixpanelService->init();
        $mp->track('sessions_per_day', ['label' => 'Sessions Per Day']);
        $location = $bookingTime->location;
        if ($location) {
            $city = $location->city;
            if ($city) {
                $mp->track('sessions_each_city', ['label' => 'Sessions Each City', 'city' => $city]);
            }
        }
    }

    public function changeStatus(Request $request)
    {

        try {
            $bookingTimeId = $request->bookingTimeId;
            $action = $request->action;
            $messageId = $request->messageId;
            $userId = $request->userId;

            $bookingTime = BookingTime::find($bookingTimeId);
            $authUser = Auth::user();
            $requestedUser = User::find($userId);
            $contactService = new ContactService();
            $t_key = '';


            if (!$requestedUser) {
                throw new \Exception('User not found.');
            }

            if (!$bookingTime) {
                throw new \Exception('Booking time request not found.');
            }

            if ($bookingTime->requester_to_user_id != $authUser->id) {
                throw new \Exception('You have nor permission for this action.');
            }

            if ($bookingTime->status == 'Accepted') {
                throw new \Exception(__('error.booking_request_already_accepted'));
            }

            if ($bookingTime->status == 'Declined') {
                $t_key = 'booking_request_already_declined';
                throw new \Exception(__('booking_request_already_declined'));
            }

            $messageFormatterService = new MessageFormatterService();

            // Message that has booking time request
            $bookingTimeMessage = Message::find($messageId);

            if (!$bookingTimeMessage) {
                throw new \Exception('Message not found.');
            }

            // Toast Message for showing after request
            $toastMessage = 'This request is still pending';

            // For accept request
            if ($action == 'accept') {
                $bookingTime->status = 'Accepted';
                $bookingTime->save();

                $requesterUser = User::find($bookingTime->requester_user_id);
                if ($requesterUser) {
                    Mail::to($requesterUser)->send(new AcceptedBookingRequest($bookingTime));
                }

                $acceptedBookingTime = new AcceptedBookingTime([
                    'bookingTimeSnapshot' => $bookingTime->toArray(),
                    'status' => 'Accepted',
                ]);

                $newMessage = new Message();
                $newMessage->message_category_id = MessageData::CATEGORY_ID_ACCEPTED_BOOKING_TIME;
                $newMessage->sender_user_id = $authUser->id;
                $newMessage->receiver_user_id = $requestedUser->id;
                $newMessage->type = 'structure';
                $newMessage->structure_content = $acceptedBookingTime->toJson();
                $newMessage->date_time = Carbon::now();
                $newMessage->date_time_iso = Carbon::now()->toISOString();
                $newMessage->save();

                $contactService->updateLastMessageAndTime($authUser, $requestedUser, $newMessage);


                $toastMessage = 'This request was successfully accepted.';
                if($t_key){
                    $t_key = 'chat_booking_time_request_accepted';
                }

                // Track accepted session
                $this->trackAcceptedSession($bookingTime);

            }

            // Decline request
            if ($action == 'decline') {
                $bookingTime->status = 'Declined';
                $bookingTime->save();

                $requesterUser = User::find($bookingTime->requester_user_id);
                if ($requesterUser) {
                    Mail::to($requesterUser)->send(new DeclinedBookingRequest($bookingTime));
                }

                $declinedBookingTime = new DeclinedBookingTime([
                    'bookingTimeSnapshot' => $bookingTime->toArray(),
                    'status' => 'Declined',
                ]);

                $newMessage = new Message();
                $newMessage->sender_user_id = $authUser->id;
                $newMessage->message_category_id = MessageData::CATEGORY_ID_DECLINED_BOOKING_TIME;
                $newMessage->receiver_user_id = $requestedUser->id;
                $newMessage->type = 'structure';
                $newMessage->structure_content = $declinedBookingTime->toJson();
                $newMessage->date_time = Carbon::now();
                $newMessage->date_time_iso = Carbon::now()->toISOString();
                $newMessage->save();

                $contactService->updateLastMessageAndTime($authUser, $requestedUser, $newMessage);

                $toastMessage = 'This request was declined.';
                if($t_key){
                    $t_key = 'chat_booking_time_request_declined';
                }

            }

            // All message
            $messages = Message::where(function ($q) use ($requestedUser, $authUser) {
                $q->where('sender_user_id', $requestedUser->id);
                $q->where('receiver_user_id', $authUser->id);
            })->orWhere(function ($q) use ($requestedUser, $authUser) {
                $q->where('sender_user_id', $authUser->id);
                $q->where('receiver_user_id', $requestedUser->id);
            })->get()->map(function ($item) use ($messageFormatterService) {
                return $messageFormatterService->doFormat($item);
            });

            return response()->json([
                'toastMessage' => $toastMessage,
                'newMessage' => $messageFormatterService->doFormat($newMessage),
                'messages'=>$messages
            ], StatusCode::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'toastMessage' => $e->getMessage(),
                't_key' => $t_key
            ], StatusCode::HTTP_UNPROCESSABLE_ENTITY);
        }
        return $request->all();
    }

}
