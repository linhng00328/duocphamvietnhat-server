<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStatusPaymentHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('status_payment_histories', function (Blueprint $table) {
            $table->id();
            $table->string("order_code")->nullable();
            $table->string("transaction_no")->nullable();
            
            $table->double("amount")->nullable();
            $table->string("bank_code")->nullable();
            $table->string("card_type")->nullable();
            $table->string("order_info")->nullable();
            $table->string("pay_date")->nullable();
            $table->string("response_code")->nullable();

            $table->string("key_code_customer")->nullable();
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
        Schema::dropIfExists('status_payment_histories');
    }
}
