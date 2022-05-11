<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContactUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contact_users', function (Blueprint $table) {
            $table->id();
            $table->string('categoryName')->nullable();
            $table->string('firstName')->nullable();
            $table->string('lastName')->nullable();
            $table->string('email');
            $table->string('status')->nullable();
            $table->text('comment')->nullable();
            $table->dateTime('lastActiveAt')->nullable();
            $table->foreignId('contactToUserId')
                ->constrained('users');
            $table->softDeletes();
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
        Schema::dropIfExists('contact_users');
    }
}
