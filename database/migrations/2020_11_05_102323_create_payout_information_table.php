<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePayoutInformationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payout_information', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id');
            $table->boolean('is_personal')->nullable();
            $table->boolean('is_company')->nullable();
            $table->text('vat_number')->nullable();
            $table->boolean('is_vat_registered')->nullable();
            $table->string('company_name')->nullable();
            $table->string('cca2')->nullable();
            $table->string('address')->nullable();
            $table->string('zip_code')->nullable();
            $table->string('city')->nullable();
            $table->string('acc_holder_name')->nullable();
            $table->string('name_of_bank')->nullable();
            $table->text('registration')->nullable();
            $table->text('account')->nullable();
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
        Schema::dropIfExists('payout_information');
    }
}
