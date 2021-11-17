<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGroupMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('group_messages', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('group_id');
            $table->string('type')->nullable();
            $table->bigInteger('message_category_id')->nullable();
            $table->bigInteger('sender_user_id');
            $table->text('content')->nullable();
            $table->dateTime('date_time');
            $table->text('date_time_iso');
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
        Schema::dropIfExists('group_messages');
    }
}
