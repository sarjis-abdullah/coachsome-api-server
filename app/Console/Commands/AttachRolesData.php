<?php

namespace App\Console\Commands;

use App\Entities\ProfileSwitch;
use App\Entities\Role;
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
    protected $signature = 'attach:role {user_id_column}';

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
        // $table_name = $this->argument('table');

        $user_id = $this->argument('user_id_column');
        $empty_roles = [];
        $switched_data_info = [];


        $columns = 'Tables_in_' . env('DB_DATABASE');//This is just to read the object by its key, DB_DATABASE is database name.
        $tables = DB::select('SHOW TABLES');

        $insert_into=array(
            // 'contacts' => 'contacts',
            'profiles' => 'profiles',
            'language_user' => 'language_user',
            'sport_category_user' => 'sport_category_user',
            'sport_tags' => 'sport_tags',
        );

        foreach ( $tables as $table ) {

            if(array_key_exists($table->$columns,$insert_into)){
              //
                $this->info("Fetching data from ".$table->$columns." where ".$user_id." is not empty...");

                $tableData = DB::table($table->$columns)->where($user_id,'!=', null )->get();

                $this->output->progressStart(count($tableData));

                foreach($tableData as $data){

                    $profile_switch_exist = ProfileSwitch::where('user_id', $data->$user_id)->exists();

                    if($profile_switch_exist){
                        $profile_data = ProfileSwitch::where('user_id', $data->$user_id)->first();
                        $role_data = Role::where('id', $profile_data->original_role)->first();
                        $role = $role_data->name;
                        DB::table($table->$columns)->where('id', $data->id)->update([
                            'user_role' => $role
                        ]);
                        array_push($switched_data_info, $data->id);
                    }else{
                        $user = User::where('id', $data->$user_id)->first();

                        if($user && $user->roles &&  !empty($user->roles[0])){

                            $role = $user->roles[0]->name;

                            DB::table($table->$columns)->where('id', $data->id)->update([
                                'user_role' => $role
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




        

    }
}
