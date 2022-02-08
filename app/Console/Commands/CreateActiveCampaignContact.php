<?php

namespace App\Console\Commands;

use App\Entities\User;
use Illuminate\Console\Command;

class CreateActiveCampaignContact extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:active-campaign-contact';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create active campaign contact .....';

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
//        $users = User::
    }
}
