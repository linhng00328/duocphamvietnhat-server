<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePointSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('point_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('store_id')->unsigned()->index();
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');

            $table->integer("point_review")->default(0)->nullable(); 
            $table->integer("point_introduce_customer")->default(0)->nullable(); 
            $table->integer("point_register_customer")->default(0)->nullable(); 
            $table->double("money_a_point")->default(0)->nullable(); 
            $table->double("percent_refund")->default(0)->nullable(); 
            $table->integer("order_max_point")->default(0)->nullable(); 
            $table->boolean("is_set_order_max_point")->default(false)->nullable(); 
            $table->boolean("allow_use_point_order")->default(false)->nullable(); 
            
            $table->boolean("bonus_point_product_to_agency")->default(false)->nullable(); 
            $table->boolean("bonus_point_bonus_product_to_agency")->default(false)->nullable(); 
            
            $table->integer("percent_use_max_point")->default(0)->nullable(); 
            $table->boolean("is_percent_use_max_point")->default(false)->nullable(); 

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
        Schema::dropIfExists('point_settings');
    }
}
