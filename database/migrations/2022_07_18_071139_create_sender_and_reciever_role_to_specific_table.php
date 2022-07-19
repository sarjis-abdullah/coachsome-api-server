<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateSenderAndRecieverRoleToSpecificTable extends Migration
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
            'bookings' => 'bookings',
            'booking_times' => 'booking_times',
            'messages' => 'messages',
        );

        foreach ( $tables as $table ) {

            if(array_key_exists($table->$columns,$insert_into)){
               // todo add it to laravel jobs, process it will queue as it will take time.
                Schema::table($table->$columns, function (Blueprint $table) {
                    $table->string('sender_user_role')->nullable();
                    $table->string('receiver_user_role')->nullable();
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
            'bookings' => 'bookings',
            'booking_times' => 'booking_times',
            'messages' => 'messages',
        );

        foreach ( $tables as $table ) {

            if(array_key_exists($table->$columns,$insert_into)){
               // todo add it to laravel jobs, process it will queue as it will take time.
                Schema::table($table->$columns, function (Blueprint $table) {
                    $table->dropColumn('sender_user_role');
                    $table->dropColumn('receiver_user_role');
                });
            }
        }
    }
}
