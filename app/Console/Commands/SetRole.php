<?php

namespace App\Console\Commands;

use App\Entities\PendingBooking;
use App\Entities\Role;
use App\Entities\User;
use Illuminate\Console\Command;

class SetRole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'set:role';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set role to user';

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
        $pendingBookings = PendingBooking::all();
        $athleteRole = Role::where('name', 'athlete')->first();
        if($athleteRole){
            foreach ($pendingBookings as $pendingBooking){
                $user = User::find($pendingBooking->customer_user_id);
                $roles = $user->roles;
                if($user && $roles->count() <1){
                    $user->attachRole($athleteRole);
                }
            }
        }
    }
}
