<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->string("image_url")->nullable();

            $table->unsignedBigInteger('store_id')->unsigned()->index();
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');

            $table->integer("position")->default('0')->nullable();
            $table->boolean('is_show_home')->default('0')->nullable();
            $table->longText("banner_ads_json")->nullable();
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

         //<table_name>_<column_name>_foreign
        Schema::table('product_discounts', function (Blueprint $table) {
            $table->dropForeign('categories_store_id_foreign');
            $table->dropColumn('store_id');
        });

        Schema::dropIfExists('categories');
    }
}
