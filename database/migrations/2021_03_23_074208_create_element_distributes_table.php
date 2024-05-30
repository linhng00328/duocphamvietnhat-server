<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateElementDistributesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('element_distributes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('store_id')->unsigned()->index();
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');
            $table->unsignedBigInteger('distribute_id')->unsigned()->index();
            $table->foreign('distribute_id')->references('id')->on('distributes')->onDelete('cascade');
            $table->unsignedBigInteger('product_id')->unsigned()->index();
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');

            $table->string("name");
            $table->string("sku")->nullable();
            $table->string("image_url")->nullable();
            $table->double("default_price")->default(0)->nullable();
            $table->string("barcode")->nullable();
            $table->double("import_price")->default(0)->nullable();
            $table->double("price")->nullable();
            $table->integer("position")->default(0)->nullable();
            $table->integer('quantity_in_stock')->default('0')->nullable();
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
        Schema::dropIfExists('element_distributes');
    }
}
