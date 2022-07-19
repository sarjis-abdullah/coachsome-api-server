<?php

namespace App\Console;

use App\Console\Commands\AttachRolesData;
use App\Console\Commands\AttachSenderRecieverRoles;
use App\Console\Commands\CreateActiveCampaignContact;
use App\Console\Commands\CreateContactUserFromExistingUser;
use App\Console\Commands\CreateWeek;
use App\Console\Commands\GenerateBalanceEarning;
use App\Console\Commands\InsertBookingSetting;
use App\Console\Commands\RunWorker;
use App\Console\Commands\SetRole;
use App\Console\Commands\UpdateDB;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        CreateWeek::class,
        SetRole ::class,
        GenerateBalanceEarning::class,
        InsertBookingSetting::class,
        UpdateDB::class,
        RunWorker::class,
        CreateActiveCampaignContact::class,
        CreateContactUserFromExistingUser::class,
        AttachRolesData::class,
        AttachSenderRecieverRoles::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('create:week')
            ->weekly();
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands/CreateContactUserFromExistingUser.php');
        require base_path('routes/console.php');
    }
}
