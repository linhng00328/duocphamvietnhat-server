<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAgencySubDisPricesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('agency_sub_dis_prices', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('store_id')->unsigned()->index();
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');

            $table->unsignedBigInteger('product_id')->unsigned()->index();
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');

            $table->unsignedBigInteger('element_distribute_id')->unsigned()->index();
            $table->foreign('element_distribute_id')->references('id')->on('element_distributes')->onDelete('cascade');
            
            $table->unsignedBigInteger('sub_element_distribute_id')->unsigned()->index();
            $table->foreign('sub_element_distribute_id')->references('id')->on('sub_element_distributes')->onDelete('cascade');
            
            $table->unsignedBigInteger('agency_type_id')->unsigned()->index();
            $table->foreign('agency_type_id')->references('id')->on('agency_types')->onDelete('cascade');

            $table->double("price")->default(0)->nullable();

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
        Schema::dropIfExists('agency_sub_dis_prices');
    }
}
