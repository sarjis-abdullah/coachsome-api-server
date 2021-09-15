<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePendingBookingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pending_bookings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('package_id')->nullable();
            $table->text('package_details')->nullable();
            $table->bigInteger('customer_user_id');
            $table->bigInteger('package_owner_user_id');
            $table->dateTime('booking_date', 0)->nullable();
            $table->dateTime('confirm_mail_date', 0)->nullable();
            $table->dateTime('activation_mail_date', 0)->nullable();
            $table->text('customer_text')->nullable();
            $table->string('customer_mobile_no')->nullable();
            $table->text('confirmation_token')->nullable();
            $table->enum('booking_status', ['Pending', 'Confirmed']);
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
