<?php

namespace App\Http\Resources\Booking;

use App\Entities\BookingTime;
use App\Services\BalanceEarningService;
use App\Services\BookingService;
use App\Services\CurrencyService;
use App\Services\Media\MediaService;
use App\Services\OrderService;
use App\Services\QuickpayClientService;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Log;
use QuickPay\QuickPay;

class BookingCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $formattedData = [];

        $currencyService = new CurrencyService();
        $quickpayClientService = new QuickpayClientService();
        $mediaService = new MediaService();
        $orderService = new OrderService();
        $bookingService = new BookingService();

        $accountId = $quickpayClientService->getAccountId();
        $quickpayClient = $quickpayClientService->getClient();

        $paymentUrl = "https://manage.quickpay.net/account/" . $accountId . "/payments/";


        foreach ($this->collection as $item) {
            $amount = 0.00;

            $packageOwnerImage = null;
            $packageBuyerImage = null;

            $packageOwnerUser = $item->packageOwnerUser;
            $packageBuyerUser = $item->packageBuyerUser;
            $order = $item->order;

            // Initial order check again
            if ($order->status == "Initial") {
                $payment = $order ? $order->payment : null;
                $paymentDetails = $order ? json_decode($payment->details) : null;
                $paymentId = $paymentDetails ? $paymentDetails->payment_id : null;
                $payment = $quickpayClient->request->get('/payments/' . $paymentId);
                $status = $payment->httpStatus();

                if ($status == 200) {
                    $paymentObject = $payment->asObject();
                    $order = $orderService->updateOrderStatusBasedOnPaymentStatus($order, $paymentObject);
                    $item = $bookingService->updateBookingStatusBasedOnOrderStatus($item, $order);

                }
            }


            $packageSnapshot = $order ? json_decode($order->package_snapshot) : '';
            $payment = $order ? $order->payment : null;
            $bookingTime = BookingTime::where('booking_id', $item->id)->where('status', 'Accepted')->get();

            $imagesForPackageOwnerUser = $mediaService->getImages($packageOwnerUser);
            $imagesForPackageBuyerUser = $mediaService->getImages($packageBuyerUser);

            if ($imagesForPackageOwnerUser['square']) {
                $packageOwnerImage = $imagesForPackageOwnerUser['square'];
            } else {
                $packageOwnerImage = $imagesForPackageOwnerUser['old'];
            }

            if ($imagesForPackageBuyerUser['square']) {
                $packageBuyerImage = $imagesForPackageBuyerUser['square'];
            } else {
                $packageBuyerImage = $imagesForPackageBuyerUser['old'];
            }

            if ($order) {
                $amount = $currencyService->format($orderService->totalPrice($order), $order->currency);
            }

            $formattedData[] = [
                'bookingId' => $item->id,
                'packageOwnerImage' => $packageOwnerImage,
                'packageBuyerImage' => $packageBuyerImage,
                'packageBuyerName' => $packageBuyerUser->first_name . ' ' . $packageBuyerUser->last_name,
                'packageOwnerName' => $packageOwnerUser->first_name . ' ' . $packageOwnerUser->last_name,
                'packageOwnerUserId' => $packageOwnerUser->id,
                'packageBuyerUserId' => $packageBuyerUser->id,
                'packageName' => $packageSnapshot ? $packageSnapshot->details->title : '',
                'bookingDate' => date('d-m-Y', strtotime($item->booking_date)),
                'status' => $order ? $order->status : '',
                'amount' => $amount,
                'paymentUrl' => $payment ? $paymentUrl . json_decode($payment->details, true)['payment_id'] : null,
                'session' => $packageSnapshot ? $bookingTime->count() . '/' . $packageSnapshot->details->session : '',
                'orderKey' => $order ? $order->key : ''
            ];
        }

        return [
            'data' => $formattedData,
            'pagination' => [
                'total' => $this->total(),
                'count' => $this->count(),
                'per_page' => $this->perPage(),
                'current_page' => $this->currentPage(),
                'total_pages' => $this->lastPage()
            ],
        ];
    }
}
