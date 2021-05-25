<?php

namespace App\Console\Commands;

use App\Data\TranslationData;
use App\Entities\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class UpdateDB extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:db';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updating database ...';

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
        if (Schema::hasTable('translations')) {
            if (!Schema::hasColumn('translations', 'type')) {
                Schema::table('translations', function (Blueprint $table) {
                    $table->string('type')->default(TranslationData::TYPE_GENERAL)->after('page_name');
                });
            }
        }


        $users = User::all();
        foreach ($users as $user) {
            $user->user_name = str_replace("-",".",$user->user_name);
            $user->save();
        }
    }
}
