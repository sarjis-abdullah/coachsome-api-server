<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExercisesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('exercises', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id');
            $table->bigInteger('exercise_asset_ids')->nullable();
            $table->string('name');
            $table->longText('instructions')->nullable();
            $table->bigInteger('category_id')->nullable();
            $table->bigInteger('sport_id')->nullable();
            $table->bigInteger('lavel_id')->nullable();
            $table->bigInteger('tags')->nullable();
            $table->integer('type');
            $table->integer('sort')->nullable();
            $table->boolean('status')->default(0);
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
        Schema::dropIfExists('exercises');
    }
}
