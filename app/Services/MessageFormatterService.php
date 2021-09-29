<?php


namespace App\Services;


use App\Entities\Booking;
use App\Entities\BookingTime;
use App\Entities\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class MessageFormatterService
{
    public function doFormat($message)
    {
        $formattedMessage = null;

        if ($message) {
            $authUser = Auth::user();

            $formattedContent = null;

            $messageId = $message->id;
            $messageType = $message->type;
            $isMe = $message->sender_user_id == $authUser->id ? true : false;
            $structureContent = json_decode($message->structure_content);
            $textContent = $message->text_content;
            $dateTimeIsoString = $message->date_time_iso;
            $categoryId = $message->message_category_id;

            // Formatting
            if ($structureContent) {
                if ($structureContent->key == 'booking_time') {
                    $bookingTime = BookingTime::find($structureContent->bookingTimeId);
                    if ($bookingTime) {
                        $structureContent->requesterUserId = $bookingTime->requester_user_id;
                        $structureContent->requesterToUserId = $bookingTime->requester_to_user_id;
                        $structureContent->status = $bookingTime->status;
                    }
                }

                // Normal booking
                if ($structureContent->key == 'booking_package') {
                    $booking = Booking::find($structureContent->bookingId);
                    if ($booking) {
                        $structureContent->status = $booking->status;
                    }
                }

                // For quick booking
                if ($structureContent->key == 'buy_package') {
                    $orderKey = "";
                    $packageTitle = "";
                    $buyerText ="";
                    $buyerName ="";
                    $packageSnapshot = null;
                    $orderSnapshot = null;
                    $status = "";
                    $session = 0;
                    $amount = 0.00;
                    $currency = "";

                    $orderSnapshot = json_decode($structureContent->orderSnapshot, true);
                    $bookingPackageSnapshot = json_decode($structureContent->packageSnapshot,true);
                    if(array_key_exists("package_snapshot", $bookingPackageSnapshot)){
                        $packageSnapshot = json_decode($bookingPackageSnapshot['package_snapshot'], true);
                        if($packageSnapshot){
                            $packageTitle = $packageSnapshot["details"]["title"];
                            $session = $packageSnapshot["details"]["session"];
                        }
                    }
                    if($orderSnapshot){
                        $booking = Booking::find($orderSnapshot['booking_id']);
                        $packageBuyerUser = $booking->packageBuyerUser;
                        if ($booking) {
                            $status = $booking->status;
                            $buyerText = $booking->package_buyer_message;
                        }
                        if($packageBuyerUser){
                            $buyerName= $packageBuyerUser->first_name ." ".$packageBuyerUser->last_anme;
                        }
                        $orderKey = $orderSnapshot["key"];
                        $currency = $orderSnapshot['currency'];
                        $amount = $orderSnapshot['total_amount'];
                    }

                    $structureContent->orderKey = $orderKey;
                    $structureContent->packageSnapshot = $packageSnapshot;
                    $structureContent->orderSnapshot = $orderSnapshot;
                    $structureContent->buyerText = $buyerText;
                    $structureContent->buyerName = $buyerName;
                    $structureContent->packageTitle = $packageTitle;
                    $structureContent->status = $status;
                    $structureContent->session = $session;
                    $structureContent->amount = $amount;
                    $structureContent->currencyCode = $currency;
                }
            }

            if ($messageType == 'text') {
                $formattedContent = $textContent;
            } else {
                $formattedContent = $structureContent;
            }

            $formattedMessage = new \stdClass();
            $formattedMessage->id = $messageId;
            $formattedMessage->categoryId = $categoryId;
            $formattedMessage->type = $messageType;
            $formattedMessage->content = $formattedContent;
            $formattedMessage->me = $isMe;
            $formattedMessage->created_at = $dateTimeIsoString;
        }

        return $formattedMessage;
    }
}
