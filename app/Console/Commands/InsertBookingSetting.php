<?php

namespace App\Console\Commands;

use App\Entities\Booking;
use App\Entities\BookingSetting;
use Illuminate\Console\Command;

class InsertBookingSetting extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'insert:booking-setting';

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
        $setting = BookingSetting::get()->first();
        $bookings = Booking::get();
        foreach($bookings as $booking){

            $booking->booking_settings_snapshot = $setting->toJson();

            if(!$booking->package_owner_service_fee_snapshot){
                $booking->package_owner_service_fee_snapshot = $setting->package_owner_gnr_service_fee;
            }

            if(!$booking->package_buyer_service_fee_snapshot){
                $booking->package_buyer_service_fee_snapshot = $setting->package_buyer_service_fee;
            }

            if(!$booking->hereof_vat_snapshot){
                $booking->hereof_vat_snapshot = $setting->hereof_vat;
            }

            $booking->save();
        }
    }
}
