<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('booking_id');
            $table->bigInteger('gift_transaction_id');
            $table->text('key' )->nullable();
            $table->bigInteger('package_id')->nullable();
            $table->bigInteger('package_category_id')->nullable();
            $table->text('package_snapshot')->nullable();
            $table->integer('number_of_attendees')->nullable();
            $table->decimal('package_sale_price', 20, 2)->nullable();
            $table->decimal('total_per_person', 20, 2)->nullable();
            $table->decimal('gift_card_amount', 20, 2)->nullable();
            $table->decimal('promo_discount', 20, 2)->nullable();
            $table->decimal('total_amount', 20, 2);
            $table->string('currency' );
            $table->decimal('service_fee', 20, 2);
            $table->string('status')->default('Initial');
            $table->dateTime('transaction_date')->nullable();
            $table->string('local_currency')->nullable();
            $table->decimal('local_total_amount', 20, 2)->nullable();
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
        Schema::dropIfExists('orders');
    }
}
