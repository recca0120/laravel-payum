<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePayumPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payum_payments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('details')->nullable();
            $table->string('number');
            $table->string('description')->nullable();
            $table->string('client_idd')->nullable();
            $table->string('client_email')->nullable();
            $table->string('total_amount')->nullable();
            $table->string('currency_code')->nullable();
            $table->text('credit_card')->nullable();
            $table->text('bank_account')->nullable();
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
        Schema::dropIfExists('payum_payments');
    }
}
