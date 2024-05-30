<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAgencyMainPriceOverridesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('agency_main_price_overrides', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('agency_type_id')->unsigned()->index();
            $table->foreign('agency_type_id')->references('id')->on('agency_types')->onDelete('cascade');
            
            $table->unsignedBigInteger('store_id')->unsigned()->index();
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');

            $table->unsignedBigInteger('product_id')->unsigned()->index();
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');

            $table->double("price")->default(0)->nullable();
            $table->double("percent_agency")->default(0)->nullable();

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
        Schema::dropIfExists('agency_main_price_overrides');
    }
}
