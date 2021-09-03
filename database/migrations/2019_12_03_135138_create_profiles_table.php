<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('profiles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id');
            $table->string('image')->nullable();
            $table->string('profile_name')->nullable();
            $table->text('about_me')->nullable();
            $table->string('mobile_no')->nullable();
            $table->string('mobile_code')->nullable();
            $table->date('birth_day')->nullable();
            $table->text('personalized_url')->nullable();
            $table->text('social_acc_fb_link')->nullable();
            $table->text('social_acc_twitter_link')->nullable();
            $table->text('social_acc_instagram_link')->nullable();
            $table->text('tag_list_id')->nullable();
            $table->text('category_list_id')->nullable();
            $table->text('language_list_id')->nullable();
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
        Schema::dropIfExists('profiles');
    }
}
