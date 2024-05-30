<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->string('name_str_filter')->nullable();
            $table->string("sku")->nullable();

            $table->unsignedBigInteger('store_id')->unsigned()->index();
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');

            $table->unique(['store_id', 'name', 'status']);

            $table->longText("video_url")->nullable();
            $table->longText("description")->nullable();

            $table->integer('likes')->default(0)->nullable();
            $table->integer('sold')->default(0)->nullable();
            $table->integer('view')->default(0)->nullable();
            $table->double("stars")->default(5)->nullable();
            $table->integer('count_stars')->default(0)->nullable();

            $table->longText("content_for_collaborator")->nullable();
            $table->integer("index_image_avatar")->default('0')->nullable();

            $table->double("price")->default(0)->nullable();
            $table->double("import_price")->default(0)->nullable();

            $table->boolean('check_inventory')->default(false)->nullable();

            $table->string("shelf_position")->nullable();


            $table->integer('quantity_in_stock')->default('-1')->nullable();

            $table->integer('type_share_collaborator_number')->default(0)->nullable();
            $table->double("money_amount_collaborator")->default('0')->nullable();
            $table->double("percent_collaborator")->default('0')->nullable();

            $table->double("min_price")->default(0)->nullable();
            $table->double("max_price")->default(0)->nullable();


            $table->string("barcode")->nullable();
            $table->integer('status')->default('0')->nullable(); //0 hiện, -1 ẩn , 1 đã xóa
            $table->longText('json_list_promotion')->nullable(); // [{content:"Noi dung khuyen mai","post_id":1, "post_name":"ten bai viet"  }]

            $table->string("point_for_agency")->nullable();
            $table->integer("weight")->nullable();

            $table->longText("seo_title")->nullable();
            $table->longText("seo_description")->nullable();

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
        Schema::dropIfExists('products');
    }
}
