<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLineItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('line_items', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('store_id')->unsigned()->index();
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');

            $table->unsignedBigInteger('order_id')->unsigned()->index();
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');

            $table->bigInteger('customer_id')->nullable();

            $table->string("phone_number")->nullable();

            $table->string("device_id")->nullable();

            $table->boolean('has_subtract_inventory')->default('0')->nullable();

            $table->unsignedBigInteger('product_id')->unsigned()->index();
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');

            $table->unsignedBigInteger('element_distribute_id')->unsigned()->nullable()->index();
            $table->foreign('element_distribute_id')->references('id')->on('element_distributes')->onDelete('cascade');

            $table->unsignedBigInteger('sub_element_distribute_id')->unsigned()->nullable()->index();
            $table->foreign('sub_element_distribute_id')->references('id')->on('sub_element_distributes')->onDelete('cascade');

            $table->integer('total_refund')->default(0)->nullable();
            $table->double("before_discount_price")->default('0')->nullable();
            $table->double("item_price")->default('0')->nullable();
            $table->double("cost_of_capital")->default('0')->nullable();
            $table->integer('quantity')->nullable();
            $table->boolean("is_refund")->default(false)->nullable();

            $table->integer("branch_id")->nullable();
            $table->longText('distributes')->nullable();
            $table->boolean('is_bonus')->default(0)->nullable();
            $table->string("parent_cart_item_ids")->nullable();
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
        Schema::dropIfExists('line_items');
    }
}
