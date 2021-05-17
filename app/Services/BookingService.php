<?php


namespace App\Services;


use App\Data\BookingStatus;
use App\Data\OrderStatus;
use App\Entities\Booking;
use App\Entities\Message;
use App\ValueObjects\Message\BuyPackage;
use App\ValueObjects\Message\PackageBooking;
use Carbon\Carbon;

class BookingService
{
    public function updateBookingStatusBasedOnOrderStatus($booking, $order)
    {

        if ($order->status == OrderStatus::AUTHORIZED) {
            $booking->status = BookingStatus::PENDING;
        }

        if ($order->status == OrderStatus::CAPTURE) {
            $booking->status = BookingStatus::ACCEPTED;
        }

        if ($order->status == OrderStatus::REJECTED) {
            $booking->status = OrderStatus::REJECTED;
        }

        if ($order->status == OrderStatus::CANCELED) {
            $booking->status = BookingStatus::DECLINED;
        }


        $booking->save();

        return $booking;
    }

    public function checkPaymentStatusOfInitialBookings($bookings)
    {
        $data = [
            "newMessages" => []
        ];

        $contactService = new ContactService();
        $messageFormatterService = new MessageFormatterService();
        $orderService = new OrderService();
        $bookingService = new BookingService();
        $quickpayClientService = new QuickpayClientService();

        foreach ($bookings as $booking) {
            $order = $booking->order;
            $packageSnapshot = $order ? json_decode($order->package_snapshot) : null;
            $packageDetails = $packageSnapshot->details;

            $payment = $order ? $order->payment : null;
            $paymentDetails = $order ? json_decode($payment->details) : null;
            $paymentId = $paymentDetails ? $paymentDetails->payment_id : null;

            $packageBuyerUser = $booking->packageBuyerUser;
            $packageOwnerUser = $booking->packageOwnerUser;

            $client = $quickpayClientService->getClient();
            $payment = $client->request->get('/payments/' . $paymentId);
            $status = $payment->httpStatus();
            if ($status == 200) {
                $paymentObject = $payment->asObject();
                $order = $orderService->updateOrderStatusBasedOnPaymentStatus($order, $paymentObject);
                $booking = $bookingService->updateBookingStatusBasedOnOrderStatus($booking, $order);
                // Rejected order do not need to send message so skip it
                if ($order->status == 'Rejected') {
                    continue;
                }
            }

            if ($booking->is_quick_booking) {
                $buyPackageMessage = new BuyPackage([
                    'orderSnapshot' => $order->toJson(),
                    'packageSnapshot' => $order->toJson(),
                    'packageBuyerName' => $packageBuyerUser->profileName(),
                    'status' => $order->status == 'Capture' ? 'Accepted' : 'Initial'
                ]);
                $newMessage = new Message();
                $newMessage->type = 'structure';
                $newMessage->sender_user_id = $packageBuyerUser->id;
                $newMessage->receiver_user_id = $packageOwnerUser->id;
                $newMessage->structure_content = $buyPackageMessage->toJson();
                $newMessage->date_time = Carbon::now();
                $newMessage->save();
            }

            if (!$booking->is_quick_booking) {
                $packageBookingMessage = new PackageBooking([
                    'packageTitle' => $packageDetails->title,
                    'orderKey' => $order->key,
                    'buyerName' => $packageBuyerUser->profileName(),
                    'amount' => $order->total_amount,
                    'currencyCode' => $order->currency,
                    'session' => $packageDetails->session,
                    'bookingId' => $booking->id,
                    'buyerText' => $booking->package_buyer_message,
                    'packageSnapshot' => json_decode($order->package_snapshot),
                    'status' => 'Pending',
                ]);
                $newMessage = new Message();
                $newMessage->type = 'structure';
                $newMessage->sender_user_id = $packageBuyerUser->id;
                $newMessage->receiver_user_id = $packageOwnerUser->id;
                $newMessage->structure_content = $packageBookingMessage->toJson();
                $newMessage->date_time = Carbon::now();
                $newMessage->save();
            }

            $contactService->updateLastMessageAndTime($packageBuyerUser, $packageOwnerUser, $newMessage);
            $data['newMessages'][] = $messageFormatterService->doFormat($newMessage);
        }

        return $data;
    }

}
