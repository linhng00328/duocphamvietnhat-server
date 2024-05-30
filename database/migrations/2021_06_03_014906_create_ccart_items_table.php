<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCcartItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ccart_items', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('store_id')->unsigned()->index();
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');

            $table->unsignedBigInteger('customer_id')->unsigned()->index()->nullable();
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');

            $table->unsignedBigInteger('product_id')->unsigned()->index();
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');

            $table->unsignedBigInteger('element_distribute_id')->unsigned()->nullable()->index();
            $table->foreign('element_distribute_id')->references('id')->on('element_distributes')->onDelete('cascade');

            $table->unsignedBigInteger('sub_element_distribute_id')->unsigned()->nullable()->index();
            $table->foreign('sub_element_distribute_id')->references('id')->on('sub_element_distributes')->onDelete('cascade');

            $table->string("device_id")->nullable();

            $table->double("before_discount_price")->default('0')->nullable();
            $table->double("item_price")->default('0')->nullable();
            $table->double("price_before_override")->default('0')->nullable();
            $table->integer('quantity')->nullable();

            $table->longText('distributes')->nullable();

            $table->bigInteger('user_id')->nullable();
            $table->bigInteger('staff_id')->nullable();
            $table->bigInteger('bonus_product_item_ladder_id')->nullable();
            $table->bigInteger('group_product_id')->nullable();
            
            $table->string("parent_cart_item_ids")->nullable();

            $table->bigInteger('list_cart_id')->nullable();
            $table->bigInteger('bonus_product_id')->nullable();
            $table->bigInteger('bonus_product_item_id')->nullable();
            $table->boolean('allows_choose_distribute')->nullable();
            $table->boolean('allows_all_distribute')->nullable();

            $table->boolean('is_bonus')->default(0)->nullable();
            $table->boolean('has_edit_item_price')->default(0)->nullable();
            $table->longText("bonus_product_name")->nullable();
            $table->longText("note")->nullable();

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
        Schema::dropIfExists('ccart_items');
    }
}
