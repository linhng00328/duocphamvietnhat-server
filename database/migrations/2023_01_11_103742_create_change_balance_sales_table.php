<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChangeBalanceSalesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('change_balance_sales', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('store_id')->unsigned()->index();
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');

            $table->unsignedBigInteger('sale_customer_id')->unsigned()->index();
            $table->foreign('sale_customer_id')->references('id')->on('sale_customers')->onDelete('cascade');

            $table->integer("type")->nullable();
            $table->double("current_balance")->default(0)->nullable();  
            $table->double("money")->default(0)->nullable();  
            $table->integer("references_id")->nullable();
            $table->string("references_value")->nullable();
            $table->string("note")->nullable(); 
            

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
        Schema::dropIfExists('change_balance_sales');
    }
}
