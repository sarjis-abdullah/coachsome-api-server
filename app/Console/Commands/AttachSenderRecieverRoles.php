<?php

namespace App\Console\Commands;

use App\Entities\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AttachSenderRecieverRoles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attach-sender-receiver:role {table} {sender_user_id_column} {receiver_user_id_column}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Attach sender and receiver user roles old Data';

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

        $sender_user_id = $this->argument('sender_user_id_column');
        $receiver_user_id = $this->argument('receiver_user_id_column');

        $empty_roles = [];


        $this->info("Fetching data from ".$table_name." where ".$sender_user_id." and ".$receiver_user_id." is not empty...");


        $tableData = DB::table($table_name)->where($sender_user_id,'!=', null )->where($receiver_user_id,'!=', null )->get();

        $this->output->progressStart(count($tableData));

        foreach($tableData as $data){

            $sender_user = User::where('id', $data->$sender_user_id)->first();

            $receiver_user = User::where('id', $data->$receiver_user_id)->first();

            if($sender_user && $sender_user->roles &&  $sender_user->roles[0]){

                $role = $sender_user->roles[0]->name;

                DB::table($table_name)->where('id', $data->id)->update([
                    'sender_user_role' => $role
                ]);

            }else{
                array_push($empty_roles, $data->id);
            }
            
            if($receiver_user && $receiver_user->roles &&  $receiver_user->roles[0]){

                $receiver_role = $receiver_user->roles[0]->name;

                DB::table($table_name)->where('id', $data->id)->update([
                    'receiver_user_role' => $receiver_role
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
