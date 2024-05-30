<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBannerAdAppsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('banner_ad_apps', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('store_id')->unsigned()->index();
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');

            $table->integer("position")->default(0)->nullable(); // trạng thái 1 chờ duyệt, 0 đã duyệt, 2 đã ẩn
            $table->string('title')->nullable();
            $table->string('type_action')->nullable();
            $table->string('value')->nullable();
            $table->string('image_url')->nullable();
            $table->boolean('is_show')->default(1)->nullable();

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
        Schema::dropIfExists('banner_ad_apps');
    }
}
