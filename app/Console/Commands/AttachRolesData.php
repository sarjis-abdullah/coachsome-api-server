<?php

namespace App\Console\Commands;

use App\Entities\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AttachRolesData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attach:role {table} {user_id_column}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Attach User roles to old Data';

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
        $table_name = $this->argument('table');
        $user_id = $this->argument('user_id_column');
        $empty_roles = [];

        $this->info("Fetching data from ".$table_name." where ".$user_id." is not empty...");

        $tableData = DB::table($table_name)->where($user_id,'!=', null )->get();

        $this->output->progressStart(count($tableData));

        foreach($tableData as $data){

            $user = User::where('id', $data->$user_id)->first();
            if($user && $user->roles &&  $user->roles[0]){

                $role = $user->roles[0]->name;

                DB::table($table_name)->where('id', $data->id)->update([
                    'user_role' => $role
                ]);

            }else{
                array_push($empty_roles, $data->id);
            }

            $this->output->progressAdvance();
        }

        $this->output->progressFinish();

        $this->info("Successfully added roles to all the column.");
        foreach($empty_roles as $empty_role){
            $this->info("No role found for the column id -> ".$empty_role." Successfully added roles to all the column.");
        }

    }
}
