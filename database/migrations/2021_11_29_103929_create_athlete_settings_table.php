<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAthleteSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('athlete_settings', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id');
            $table->integer('inbox_message')->nullable();
            $table->integer('order_message')->nullable();
            $table->integer('order_update');
            $table->integer('booking_request');
            $table->integer('booking_change');
            $table->integer('account');
            $table->integer('marketting');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('athlete_settings');
    }
}
