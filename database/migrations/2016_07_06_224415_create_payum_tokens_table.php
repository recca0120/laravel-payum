<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePayumTokensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payum_tokens', function (Blueprint $table) {
            $table->string('hash')->primary();
            $table->text('details')->nullable();
            $table->string('target_url')->nullable();
            $table->string('after_url')->nullable();
            $table->string('gateway_name');
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
        Schema::dropIfExists('payum_tokens');
    }
}
