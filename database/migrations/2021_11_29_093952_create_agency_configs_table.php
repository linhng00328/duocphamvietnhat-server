<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAgencyConfigsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('agency_configs', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('store_id')->unsigned()->nullable();
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');   

            $table->boolean("allow_payment_request")->default(false)->nullable();

            $table->boolean("payment_1_of_month")->default(false)->nullable();

            $table->boolean("payment_16_of_month")->default(false)->nullable();

            $table->double("payment_limit")->default(0)->nullable();
            
            $table->boolean("allow_rose_referral_customer")->default(false)->nullable();

            $table->double("percent_agency_t1")->default(0)->nullable();

            $table->integer("bonus_type_for_ctv_t2")->default(0)->nullable();

            $table->integer("type_bonus_period_import")->default(0)->nullable();
            
            $table->boolean("auto_set_level_agency")->default(false)->nullable();
            $table->integer("auto_set_type_period")->default(0)->nullable();


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
        Schema::dropIfExists('agency_configs');
    }
}
