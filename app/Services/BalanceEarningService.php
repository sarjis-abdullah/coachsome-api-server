<?php


namespace App\Services;


use App\Entities\Booking;
use App\Entities\BookingSetting;
use App\Entities\PayoutRequest;
use App\Services\Mixpanel\MixpanelService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class BalanceEarningService
{
    public function getUserBalanceEarningInfo($authUser, $userCurrency)
    {
        $rows = [];
        $results = [];

        // Top level scope
        $G_SAVINGS = 0.00;
        $G_BALANCE = 0.00;
        $G_PAID = 0.00;

        $currencyService = new CurrencyService();

        $bookings = Booking::where('status', 'Accepted')
            ->where('package_owner_user_id', $authUser->id)
            ->orderBy('booking_date', 'ASC')
            ->with([
                'order', 'order.payment', 'packageOwnerUser', 'packageBuyerUser', 'bookingTimes'
            ])->get();

        foreach ($bookings as $booking) {
            $booking->common_type = 'PACKAGE_BOOKING';
            $booking->common_date = date('Y-m-d H:i:s', strtotime($booking->booking_date));
            $rows[] = $booking;
            $bookingTimes = $booking->bookingTimes->where('status', 'Accepted');
            foreach ($bookingTimes as $bookingTime) {
                $bookingTime->common_type = 'SESSION_BOOKING';
                $bookingTime->common_date = date('Y-m-d H:i:s', strtotime($bookingTime->created_at));
                $rows[] = $bookingTime;
            }
        }

        $payouts = PayoutRequest::where('user_id', $authUser->id)->where('status', 'Paid')->get();

        // Merge all entities
        foreach ($payouts as $payout) {
            $payout->common_type = 'PAYOUT';
            $payout->common_date = date('Y-m-d H:i:s', strtotime($payout->date_with_time));
            $rows[] = $payout;
        }

        // Sort by date
        $rowCollection = collect($rows)->sortBy(function ($item) {
            return Carbon::parse($item['common_date'])->getTimestamp();
        })->values()->all();


        foreach ($rowCollection as $index => $item) {

            // Booking
            if ($item->common_type == 'PACKAGE_BOOKING') {
                $packageBuyerUser = $item->packageBuyerUser;
                $order = $item->order;

                $bookingSetting = json_decode($item->booking_settings_snapshot);

                $packageSnapshot = $order
                    ? ($order->package_snapshot ? json_decode($order->package_snapshot) : null)
                    : null;

                $rate = 0.00;
                $date = date('d-m-Y', strtotime($item->booking_date));
                $description = '';
                $customerName = '';
                $amount = 0.00;
                $currency = $order ? $order->currency : 'DKK';
                $fee = 0.00;
                $income = 0.00;


                if ($packageBuyerUser) {
                    $customerName = $packageBuyerUser->first_name . ' ' . $packageBuyerUser->last_name;
                }

                if ($bookingSetting) {
                    $rate = $bookingSetting->package_owner_gnr_service_fee / 100;
                }

                if ($packageSnapshot) {
                    if ($packageSnapshot->details) {
                        $description = $packageSnapshot->details->title;
                    }
                }

                if ($order) {
                    $amount = round($currencyService->convert($order->package_sale_price, $currency, $userCurrency), 2);
                    $fee = round(($amount * $rate), 2);
                    $income = round(($amount - $fee), 2);
                    $G_SAVINGS = round(($G_SAVINGS + $income), 2);
                }

                $packageBookingItem = new \App\ValueObjects\Account\BalanceEarning();
                $packageBookingItem->id = ++$index;
                $packageBookingItem->date = $date;
                $packageBookingItem->description = $description;
                $packageBookingItem->customerName = $customerName;
                $packageBookingItem->amount = $amount;
                $packageBookingItem->currency = $currency;
                $packageBookingItem->fee = $fee;
                $packageBookingItem->income = $income;
                $packageBookingItem->savings = $G_SAVINGS;
                $packageBookingItem->savingsToBalanceTransferredAmount = 0.00;
                $packageBookingItem->balance = $G_BALANCE;
                $packageBookingItem->paid = 0.00;
                $results['overviews'][] = $packageBookingItem;
            }

            // Session
            if ($item->common_type == 'SESSION_BOOKING') {
                $booking = $item->booking;
                $packageBuyerUser = $booking->packageBuyerUser;
                $order = $booking->order;

                $packageSnapshot = $order
                    ? ($order->package_snapshot ? json_decode($order->package_snapshot) : null)
                    : null;

                $rate = 0.00;
                $date = date('d-m-Y', strtotime($item->created_at));
                $description = '';
                $customerName = $packageBuyerUser ? $packageBuyerUser->first_name . ' ' . $packageBuyerUser->last_name : '';
                $amount = 0.00;
                $currency = $order ? $order->currency : 'DKK';
                $income = 0.00;

                $bookingSetting = BookingSetting::first();
                
                if ($bookingSetting) {
                    $rate = $bookingSetting->package_owner_gnr_service_fee / 100;
                }else{
                    $bookingSetting = BookingSetting::first();
                    $rate = $bookingSetting->package_owner_gnr_service_fee / 100;
                }


                if ($order) {
                    $amount = round($currencyService->convert($order->package_sale_price, $currency, $userCurrency), 2);
                    $fee = $amount * $rate;
                    $income = $amount - $fee;
                }


                $savingToBalanceTransferredAmount = 0.00;
                if ($packageSnapshot) {
                    $description = 'Booking ' . '1/' . $packageSnapshot->details->session . ' from ' . $packageSnapshot->details->title;
                    $savingToBalanceTransferredAmount = round($income / $packageSnapshot->details->session, 2);
                    $G_SAVINGS = round(($G_SAVINGS - $savingToBalanceTransferredAmount), 2);
                    $G_BALANCE = round(($G_BALANCE + $savingToBalanceTransferredAmount), 2);

                }

                $packageBookingTimeItem = new \App\ValueObjects\Account\BalanceEarning();
                $packageBookingTimeItem->id = ++$index;
                $packageBookingTimeItem->balance = $G_BALANCE;
                $packageBookingTimeItem->description = $description;
                $packageBookingTimeItem->date = $date;
                $packageBookingTimeItem->customerName = $customerName;
                $packageBookingTimeItem->amount = 0.00;
                $packageBookingTimeItem->currency = $currency;
                $packageBookingTimeItem->savings = $G_SAVINGS;
                $packageBookingTimeItem->balance = $G_BALANCE;
                $packageBookingTimeItem->savingsToBalanceTransferredAmount = $savingToBalanceTransferredAmount;
                $packageBookingTimeItem->paid = 0.00;
                $results['overviews'][] = $packageBookingTimeItem;

            }

            // Payout
            if ($item->common_type == 'PAYOUT') {
                $payoutItem = $item;

                $packageOwnerUser = $payoutItem->user;
                $date = date('d-m-Y', strtotime($payoutItem->common_date));
                $description = 'Payout to you';
                $customerName = $packageOwnerUser->first_name . ' ' . $packageOwnerUser->last_name;
                $balanceToPaidTransferredAmount = $currencyService->convert($payoutItem->amount, $payoutItem->currency, $userCurrency);
                $G_BALANCE = $G_BALANCE - $balanceToPaidTransferredAmount;
                $G_PAID = $G_PAID + $balanceToPaidTransferredAmount;

                // Balance review here
                // If it less than 1 then it has to add to transferred amount
                if (round($G_BALANCE, 2) < 1) {
                    $balanceToPaidTransferredAmount = $balanceToPaidTransferredAmount + $G_BALANCE;
                    $G_BALANCE = 0.00;
                }


                $packageBookingTimeItem = new \App\ValueObjects\Account\BalanceEarning();
                $packageBookingTimeItem->balance = $G_BALANCE;
                $packageBookingTimeItem->description = $description;
                $packageBookingTimeItem->id = ++$index;
                $packageBookingTimeItem->date = $date;
                $packageBookingTimeItem->customerName = $customerName;
                $packageBookingTimeItem->amount = 0.00;
                $packageBookingTimeItem->currency = $payoutItem->currency;
                $packageBookingTimeItem->savings = $G_SAVINGS;
                $packageBookingTimeItem->balance = $G_BALANCE;
                $packageBookingTimeItem->balanceToPaidTransferredAmount = round($balanceToPaidTransferredAmount, 2);
                $packageBookingTimeItem->paid = round($balanceToPaidTransferredAmount, 2);
                $results['overviews'][] = $packageBookingTimeItem;

            }

        }

        $lastPayoutRequest = PayoutRequest::where('user_id', $authUser->id)->get()->last();

        $results['currentBalance']['amount'] = $G_BALANCE;
        $results['currentBalance']['currency'] = $userCurrency;
        $results['payoutRequest']['last'] = $lastPayoutRequest;

        return $results;
    }
}
