<?php

namespace App\Console\Commands;

use App\Entities\User;
use App\Entities\UserDefWeekAvailability;
use App\Entities\UserWeekAvailability;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CreateWeek extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:week';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create new weeks for every user';

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
        $users = User::has('defaultAvailability')->get();
        Log::info("================================================================Create Availability =====================================================");

        $users->each(function($item){
            $now = Carbon::now();
            $currentWeek = $now->weekOfYear;
            $weekStartDate = $now->startOfWeek();

            $defAvailability = $item->defaultAvailability;
            if($defAvailability){
                for ($i = 0; $i < 3; $i++) {
                    $existedAvailability = UserWeekAvailability::where('user_id', $item->id)
                        ->where('week_no', $currentWeek)
                        ->where('week_start_date',  $weekStartDate->format('Y-m-d'))
                        ->first();
                    if(!$existedAvailability){
                        $availability = new UserWeekAvailability();
                        $availability->user_id = $item->id;
                        $availability->text = "Week " . $currentWeek;
                        $availability->week_no = $currentWeek;
                        $availability->week_start_date = $weekStartDate->format('Y-m-d');
                        $availability->days = $defAvailability->days;
                        $availability->is_fewer_time = $defAvailability->is_fewer_time;
                        $availability->save();
                    }
                    $weekStartDate->modify('+7 days');
                    $currentWeek = $weekStartDate->weekOfYear;
                }

            }
        });


    }
}
