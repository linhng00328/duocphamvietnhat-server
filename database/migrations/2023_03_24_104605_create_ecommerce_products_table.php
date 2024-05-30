<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEcommerceProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ecommerce_products', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('store_id')->unsigned()->index();
      
            $table->string("name");
            $table->string('name_str_filter')->nullable();

            $table->string("product_id_in_ecommerce")->nullable();
            $table->string("sku_in_ecommerce")->nullable();


            $table->longText("video_url")->nullable();
            $table->longText("description")->nullable();


            $table->integer("index_image_avatar")->default('0')->nullable();

            $table->double("price")->default(0)->nullable();
            $table->double("import_price")->default(0)->nullable();



            $table->double("percent_collaborator")->default('0')->nullable();
            $table->double("min_price")->default(0)->nullable();
            $table->double("max_price")->default(0)->nullable();

            $table->string("barcode")->nullable();
            $table->integer('status')->default('0')->nullable(); //0 hiện, -1 ẩn , 1 đã xóa
            $table->longText('json_images')->nullable(); //json ảnh

            $table->longText("seo_title")->nullable();
            $table->longText("seo_description")->nullable();

            $table->string("from_platform")->nullable();
            $table->string("shop_id")->nullable();
            $table->string("shop_name")->nullable();
            $table->string("code")->nullable();

            $table->unique(['shop_id', 'name', 'status'], "uniname2");

            $table->boolean("is_element")->nullable();
            $table->boolean("is_sub_element")->nullable();
            $table->integer("parent_product_id_in_ecommerce")->nullable();
            $table->string("parent_sku_in_ecommerce")->nullable();

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
        Schema::dropIfExists('ecommerce_products');
    }
}
