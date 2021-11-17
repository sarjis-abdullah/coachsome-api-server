<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('sender_user_id');
            $table->bigInteger('receiver_user_id');
            $table->bigInteger('booking_time_id')->nullable();
            $table->bigInteger('message_category_id')->nullable();
            $table->string('type')->nullable();
            $table->text('text_content')->nullable();
            $table->text('structure_content')->nullable();
            $table->dateTime('date_time');
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
        Schema::dropIfExists('messages');
    }
}
