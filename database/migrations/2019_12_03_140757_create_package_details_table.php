<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePackageDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('package_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('package_id');
            $table->string('title');
            $table->text('description');
            $table->string('session')->nullable();
            $table->string('time_per_session')->nullable();
            $table->integer('attendees_min')->unsigned()->nullable();
            $table->integer('attendees_max')->unsigned()->nullable();
            $table->string('completed_by_days')->nullable();
            $table->double('price',20,2);
            $table->boolean('is_special_price')->default(0)->nullable();
            $table->double('discount')->nullable();
            $table->double('transport_fee')->nullable();
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
        Schema::dropIfExists('package_details');
    }
}
