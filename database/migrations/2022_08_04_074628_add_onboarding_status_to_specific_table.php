<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddOnboardingStatusToSpecificTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $columns = 'Tables_in_' . env('DB_DATABASE');//This is just to read the object by its key, DB_DATABASE is database name.
        $tables = DB::select('SHOW TABLES');

        $insert_into=array(
            'locations' => 'locations',
            'profiles' => 'profiles',
            'language_user' => 'language_user',
            'sport_category_user' => 'sport_category_user',
            'sport_tags' => 'sport_tags',
            'users' => 'users',
        );

        foreach ( $tables as $table ) {

            if(array_key_exists($table->$columns,$insert_into)){
               // todo add it to laravel jobs, process it will queue as it will take time.
                Schema::table($table->$columns, function (Blueprint $table) {
                    $table->string('is_onboarding')->default(0)->nullable();
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $columns = 'Tables_in_' . env('DB_DATABASE');//This is just to read the object by its key, DB_DATABASE is database name.
        $tables = DB::select('SHOW TABLES');

        $insert_into=array(
            'locations' => 'locations',
            'profiles' => 'profiles',
            'language_user' => 'language_user',
            'sport_category_user' => 'sport_category_user',
            'sport_tags' => 'sport_tags',
            'users' => 'users',
        );

        foreach ( $tables as $table ) {

            if(array_key_exists($table->$columns,$insert_into)){
               // todo add it to laravel jobs, process it will queue as it will take time.
                Schema::table($table->$columns, function (Blueprint $table) {
                    $table->dropColumn('is_onboarding');
                });
            }
        }
    }
}
