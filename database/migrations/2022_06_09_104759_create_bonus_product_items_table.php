<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBonusProductItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bonus_product_items', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('store_id')->unsigned()->index();
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');

            $table->unsignedBigInteger('bonus_product_id')->unsigned()->index();
            $table->foreign('bonus_product_id')->references('id')->on('bonus_products')->onDelete('cascade');

            $table->unsignedBigInteger('product_id')->unsigned()->index();
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');

            $table->unsignedBigInteger('element_distribute_id')->unsigned()->nullable()->index();
            $table->foreign('element_distribute_id')->references('id')->on('element_distributes')->onDelete('cascade');

            $table->unsignedBigInteger('sub_element_distribute_id')->unsigned()->nullable()->index();
            $table->foreign('sub_element_distribute_id')->references('id')->on('sub_element_distributes')->onDelete('cascade');

            $table->boolean("is_select_product")->default(1)->nullable();
            $table->integer("group_product")->default(0)->nullable();
            $table->boolean("allows_choose_distribute")->default(1)->nullable();
            $table->boolean("allows_all_distribute")->default(0)->nullable();


            $table->integer("quantity")->default(1)->nullable();

            $table->string("distribute_name")->nullable();
            $table->string("element_distribute_name")->nullable();
            $table->string("sub_element_distribute_name")->nullable();

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
        Schema::dropIfExists('bonus_product_items');
    }
}
