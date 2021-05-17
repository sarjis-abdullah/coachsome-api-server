<?php

namespace App\Console\Commands;

use App\Entities\Setting;
use App\Entities\User;
use App\Entities\UserSetting;
use Illuminate\Console\Command;

class UpdatePackageOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:package-order';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update all user setting ....';

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
     * @return int
     */
    public function handle()
    {
        $users = User::get();
        foreach ($users as $user) {
            $packages = $user->packages;
            foreach ($packages as $i => $package) {
                $package->order = ++$i;
                $package->save();
            }
        }
    }
}
