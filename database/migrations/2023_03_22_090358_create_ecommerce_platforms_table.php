<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEcommercePlatformsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ecommerce_platforms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('store_id')->unsigned()->index();
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');

            $table->string("platform")->nullable();

            $table->string("shop_id")->nullable();
            $table->string("shop_isd")->nullable();
            $table->string("shop_name")->nullable();
            $table->integer('type_sync_products')->nullable()->default(0); //0 luôn lấy sp từ tiki về, đợi ghép sp giữa 2 bên
            $table->integer('type_sync_inventory')->nullable()->default(0); //0 cập nhật kho, 1 cập nhật tồn kho

            $table->integer('type_sync_orders')->nullable()->default(0); //0 tự động, 1 thủ công

            $table->string("customer_name")->nullable();
            $table->string("customer_phone")->nullable();

            $table->timestamp('expiry_token')->nullable(); // thời gian bắt đầu minigame 
            $table->string("token")->nullable();
            $table->string("refresh_token")->nullable();
            $table->string("token_type")->nullable();
            $table->string("scope")->nullable();

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
        Schema::dropIfExists('ecommerce_platforms');
    }
}
