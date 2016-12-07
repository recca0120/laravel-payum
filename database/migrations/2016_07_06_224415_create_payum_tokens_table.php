<?php

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
            $table->string('targetUrl')->nullable();
            $table->string('afterUrl')->nullable();
            $table->string('gatewayName');
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
