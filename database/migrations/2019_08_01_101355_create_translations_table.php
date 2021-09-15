<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('translations', function (Blueprint $table) {

            $table->collation = 'utf8mb4_bin';
            $table->bigIncrements('id');
            $table->integer('status')->default(0);
            $table->string('locale')->nullable();

            $table->string('group')->nullable();
            $table->string('page_name')->nullable();
            $table->text('gl_key')->unique();
            $table->text('en_value')->nullable();
            $table->text('dn_value')->nullable();
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
        Schema::dropIfExists('translations');
    }
}
