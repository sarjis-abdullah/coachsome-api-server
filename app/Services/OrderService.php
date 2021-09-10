<?php


namespace App\Services;


use App\Data\OrderStatus;
use App\Entites\PromoUser;

class OrderService
{
    public function totalPrice($order)
    {
        return $order->package_sale_price * $order->number_of_attendees;
    }

    public function serviceFee($order)
    {
        return $order->service_fee * $order->number_of_attendees;
    }

    public function grandTotal($order)
    {
        return $this->totalPrice($order) + $this->serviceFee($order) - $this->promoDiscount($order);
    }

    public function promoDiscount($order)
    {
        return $order->promo_discount;
    }

    public function promoCode($order)
    {
        $promoUser = PromoUser::where('order_id', $order->id)->first();
        return $promoUser ? $promoUser->code : "";
    }

    public function vat($order)
    {
        $rate = 0.00;
        $booking = $order->booking;
        if ($booking) {
            $rate = $booking->hereof_vat_snapshot ? $booking->hereof_vat_snapshot / 100 : 0.00;
        }
        return $this->grandTotal($order) * $rate;
    }

    public function packageQty($order)
    {
        return $order->number_of_attendees;
    }

    public function serviceFeeQty($order)
    {
        return $order->number_of_attendees;
    }

    public function updateOrderStatusBasedOnPaymentStatus($order, $paymentObject)
    {
        // Rejected condition
        if (!$paymentObject->accepted) {
            $order->status = OrderStatus::REJECTED;
            $order->save();
        }

        // Authorized condition
        if ($paymentObject->accepted && $paymentObject->state != "processed") {
            $order->status = OrderStatus::AUTHORIZED;
            $order->save();

        }

        // Capture Condition
        if ($paymentObject->accepted && $paymentObject->state == "processed") {
            $order->status = OrderStatus::CAPTURE;
            $order->save();
        }

        return $order;
    }

}
