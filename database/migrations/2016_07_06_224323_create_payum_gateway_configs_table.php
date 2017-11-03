<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

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
            $table->string('factory_name');
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
        Schema::dropIfExists('payum_gateway_configs');
    }
}
