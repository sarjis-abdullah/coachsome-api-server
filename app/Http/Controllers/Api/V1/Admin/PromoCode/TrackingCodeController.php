<?php

namespace App\Http\Controllers\Api\V1\Admin\PromoCode;

use App\Data\StatusCode;
use App\Entites\PromoUser;
use App\Entities\Order;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TrackingCodeController extends Controller
{
    public function index(Request $request, $code)
    {
        try {
            $promoUsers = PromoUser::where('code', $code)->get();
            $trackingCodes = $promoUsers->map(function ($item) {
                $orderId = null;
                $customerName = "";
                $coachName = "";
                $orderDate = "";
                $packageName = "";
                $status = "";
                $amount = "";

                $order = Order::find($item->order_id);
                if ($order) {
                    $status = $order->status;
                    $amount = $order->currency.' '.$order->promo_discount;
                    $booking = $order->booking;
                    $package = json_decode($order->package_snapshot);
                    if($package){
                        $packageName = $package->details->title ?? "";
                    }
                    if($booking){
                        $orderDate = date('d-m-Y', strtotime($booking->booking_date));
                        $orderId = $order->key;
                        $packageOwnerUser = $booking->packageOwnerUser;
                        $packageBuyerUser = $booking->packageBuyerUser;
                        if($packageOwnerUser){
                            $coachName = $packageOwnerUser->first_name.' '.$packageOwnerUser->last_name;
                        }
                        if($packageBuyerUser){
                            $customerName = $packageBuyerUser->first_name.' '.$packageBuyerUser->last_name;
                        }
                    }
                }


                return [
                    'orderId' => $orderId,
                    'customerName' => $customerName,
                    'coachName' => $coachName,
                    'orderDate' => $orderDate,
                    'packageName' => $packageName,
                    'status' => $status,
                    'amount' => $amount,
                ];
            });
            return response(["data" => $trackingCodes], StatusCode::HTTP_OK);
        } catch (\Exception $e) {
            return response(['message' => $e->getMessage()], StatusCode::HTTP_UNPROCESSABLE_ENTITY);
        }

    }
}
