<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGiftOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gift_orders', function (Blueprint $table) {
            $table->id();
            $table->text('key')->nullable();
            $table->bigInteger('user_id');
            $table->bigInteger('promo_code_id');
            $table->text('message')->nullable();
            $table->text('recipent_name')->nullable();
            $table->string('currency');
            $table->decimal('total_amount', 20, 2);
            $table->string('status');
            $table->dateTime('order_date');
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
        Schema::dropIfExists('gift_orders');
    }
}
