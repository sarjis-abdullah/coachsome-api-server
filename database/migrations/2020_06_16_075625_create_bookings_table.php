<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBookingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('package_owner_user_id');
            $table->bigInteger('package_buyer_user_id');
            $table->text('package_buyer_message')->nullable();
            $table->text('booking_settings_snapshot')->nullable();
            $table->dateTime('booking_date')->nullable();
            $table->dateTime('date_of_acceptance')->nullable();
            $table->dateTime('date_of_decline')->nullable();
            $table->boolean('is_quick_booking' );
            $table->boolean('is_favourite' )->default(0);
            $table->string('status')->default('Initial');
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
        Schema::dropIfExists('bookings');
    }
}
