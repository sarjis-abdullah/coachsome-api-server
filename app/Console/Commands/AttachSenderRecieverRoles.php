<?php

namespace App\Console\Commands;

use App\Entities\ProfileSwitch;
use App\Entities\Role;
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
        $switched_data_info = [];


        $this->info("Fetching data from ".$table_name." where ".$sender_user_id." and ".$receiver_user_id." is not empty...");


        $tableData = DB::table($table_name)->where($sender_user_id,'!=', null )->where($receiver_user_id,'!=', null )->get();

        $this->output->progressStart(count($tableData));

        foreach($tableData as $data){

            $sender_user = User::where('id', $data->$sender_user_id)->first();

            $receiver_user = User::where('id', $data->$receiver_user_id)->first();

            $profile_switch_exist_for_sender = ProfileSwitch::where('user_id', $data->$sender_user_id)->exists();
            

            if($profile_switch_exist_for_sender){
                $profile_data = ProfileSwitch::where('user_id', $data->$sender_user_id)->first();
                $role_data = Role::where('id', $profile_data->original_role)->first();
                $role = $role_data->name;
                DB::table($table_name)->where('id', $data->id)->update([
                    'sender_user_role' => $role
                ]);
                array_push($switched_data_info, $data->id);
            }else{
                if($sender_user && $sender_user->roles &&  $sender_user->roles[0]){

                    $role = $sender_user->roles[0]->name;
    
                    DB::table($table_name)->where('id', $data->id)->update([
                        'sender_user_role' => $role
                    ]);
    
                }else{
                    array_push($empty_roles, $data->id);
                }
            }

            $profile_switch_exist_for_receiver = ProfileSwitch::where('user_id', $data->$receiver_user_id)->exists();

            if($profile_switch_exist_for_receiver){
                $receiver_profile_data = ProfileSwitch::where('user_id', $data->$receiver_user_id)->first();
                $receiver_role_data = Role::where('id', $receiver_profile_data->original_role)->first();
                $role = $receiver_role_data->name;
                DB::table($table_name)->where('id', $data->id)->update([
                    'receiver_user_role' => $role
                ]);
                array_push($switched_data_info, $data->id);
            }else{
                if($receiver_user && $receiver_user->roles &&  $receiver_user->roles[0]){

                    $receiver_role = $receiver_user->roles[0]->name;
    
                    DB::table($table_name)->where('id', $data->id)->update([
                        'receiver_user_role' => $receiver_role
                    ]);
    
                }else{
                    array_push($empty_roles, $data->id);
                }
            }

            $this->output->progressAdvance();
        }

        $this->output->progressFinish();

        $this->info("Successfully added roles to all the column.");
        $this->info("No role found for the column ids -> ".implode(", ",$empty_roles)." Successfully added roles to all the column.");
        $this->info("This column ids -> ".implode(", ",$switched_data_info)." has been collected from profile switch.");

    }
}
