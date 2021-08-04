<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class RunWorker extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run:worker';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Queue worker is running ... ';

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
        return $this->call('queue:work', [
            '--stop-when-empty' => null,
        ]);
    }
}
