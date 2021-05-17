<?php

namespace App\Console\Commands;

use App\Entities\BalanceEarning;
use App\Entities\Booking;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class GenerateBalanceEarning extends Command
{
    private const TYPE_PACKAGE_BOOKING = 'PACKAGE_BOOKING';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:balance-earning';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $acceptedBookings = Booking::where('status', 'Accepted')
            ->orderBy('booking_date', 'ASC')
            ->with([
            'order', 'order.payment', 'packageOwnerUser', 'packageBuyerUser', 'bookingTimes'
        ])->get()->each(function ($acceptedBooking){

            $packageOwnerUser = $acceptedBooking->packageOwnerUser;
            $packageBuyerUser = $acceptedBooking->packageBuyerUser;
            $order = $acceptedBooking->order;

            $bookingSetting = json_decode($acceptedBooking->booking_settings_snapshot);

            $packageSnapshot = $order
                ? ($order->package_snapshot ? json_decode($order->package_snapshot) : null)
                : null;



            $type = self::TYPE_PACKAGE_BOOKING;
            $rate = 0.00;

            $date = date('Y-m-d', strtotime($acceptedBooking->booking_date));
            $dateWithTime = $acceptedBooking->booking_date;
            $description = '';
            $customerName = '';
            $amount = 0.00;
            $currency = 'DKK';
            $fee = 0.00;
            $income = 0.00;
            $savings = 0.00;
            $balance = 0.00;
            $paid = 0.00;


            if($packageBuyerUser){
                $customerName = $packageBuyerUser->first_name.' '. $packageBuyerUser->last_name;
            }

            if ($bookingSetting) {
                $rate = $bookingSetting->package_owner_gnr_service_fee / 100;
            }

            if ($packageSnapshot) {
                if ($packageSnapshot->details) {
                    $description =  $packageSnapshot->details->title;
                }
            }

            if ($order) {
                $amount = $order->package_sale_price;
                $currency = $order->currency;
                $fee = ($order->package_sale_price) * $rate;
                $income = $order->package_sale_price - $fee;
            }

            // Store balance and eran
            $balanceEarn = new BalanceEarning();
            $balanceEarn->type = $type;
            $balanceEarn->package_owner_user_id = $packageOwnerUser->id;
            $balanceEarn->package_buyer_user_id = $packageBuyerUser->id;
            $balanceEarn->date = $date;
            $balanceEarn->date_with_time = $dateWithTime;
            $balanceEarn->description = $description;
            $balanceEarn->amount = $amount;
            $balanceEarn->currency = $currency;
            $balanceEarn->fee = $fee;
            $balanceEarn->income = $income;
            $balanceEarn->save();
        });
    }
}
