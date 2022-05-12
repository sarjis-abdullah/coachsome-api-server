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
            $table->text('exercise_asset_ids')->nullable();
            $table->string('name');
            $table->longText('instructions')->nullable();
            $table->text('category_id')->nullable();
            $table->text('sport_id')->nullable();
            $table->text('lavel_id')->nullable();
            $table->longText('tags')->nullable();
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
