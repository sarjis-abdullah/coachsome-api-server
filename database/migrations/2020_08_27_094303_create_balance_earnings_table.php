<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBalanceEarningsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('balance_earnings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('type');
            $table->bigInteger('package_owner_user_id');
            $table->bigInteger('package_buyer_user_id');

            $table->dateTime('date_with_time')->nullable();
            $table->date('date')->nullable();
            $table->text('description')->nullable();
            $table->string('currency')->nullable();
            $table->decimal('amount',20, 2)->nullable();
            $table->decimal('fee', 20, 2)->nullable();
            $table->decimal('income' ,20, 2)->nullable();
            $table->decimal('savings' ,20, 2)->nullable();
            $table->decimal('balance' ,20, 2)->nullable();
            $table->decimal('paid' ,20, 2)->nullable();

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
        Schema::dropIfExists('balance_earnings');
    }
}
