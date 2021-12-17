<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGiftPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gift_payments', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('gift_order_id');
            $table->string('service_provider');
            $table->string('method');
            $table->text('details');
            $table->text('authorization_link');
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
        Schema::dropIfExists('gift_payments');
    }
}
