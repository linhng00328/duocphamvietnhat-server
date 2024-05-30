<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSanCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('san_categories', function (Blueprint $table) {
            $table->id();

            $table->string("name")->nullable();
            $table->string("image_url")->nullable();
            $table->integer("category_index")->default(0)->nullable();
            $table->integer("parent_id")->default(0)->nullable();

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
        Schema::dropIfExists('san_categories');
    }
}
