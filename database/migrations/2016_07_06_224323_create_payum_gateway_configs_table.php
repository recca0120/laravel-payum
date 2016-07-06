<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePayumGatewayConfigsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payum_gateway_configs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('config');
            $table->string('factoryName');
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
        Schema::drop('payum_gateway_configs');
    }
}
