<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('full_name')->nullable();
            $table->string('email')->unique();
            $table->string('user_name')->unique()->nullable();
            $table->string('password')->nullable();
            $table->boolean('verified')->default(0);
            $table->boolean('is_online')->default(0);
            $table->boolean('agree_to_terms')->default(1);
            $table->bigInteger('activity_status_id')->default(1);
            $table->string('activity_status_reason')->nullable();
            $table->bigInteger('star_status_id')->nullable();
            $table->bigInteger('badge_id')->default(1);
            $table->softDeletes();
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}
