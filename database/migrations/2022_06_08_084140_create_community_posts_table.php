<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommunityPostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('community_posts', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('store_id')->unsigned()->index();
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');

            $table->unsignedBigInteger('user_id')->unsigned()->index()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->nullable();

            $table->unsignedBigInteger('staff_id')->unsigned()->index()->nullable();
            $table->foreign('staff_id')->references('id')->on('staff')->onDelete('cascade')->nullable();

            $table->unsignedBigInteger('customer_id')->unsigned()->index()->nullable();
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade')->nullable();

            $table->string("name")->nullable();
            $table->string("name_str_filter")->nullable();

            $table->string("feeling")->nullable();
            $table->string("checkin_location")->nullable();
            $table->string("background_color")->nullable();

            $table->integer("likes")->default(0)->nullable();

            $table->longText("content")->nullable();

            $table->integer("time_repost")->nullable();

            $table->integer("position_pin")->nullable();

            $table->integer("status")->default(1)->nullable(); // trạng thái 1 chờ duyệt, 0 đã duyệt, 2 đã ẩn
            $table->longText("images_json")->nullable();

            $table->boolean("is_pin")->nullable();
            $table->integer("privacy")->default(0)->nullable(); //0 tất cả //1 chỉ mình tôi //2 bạn bè

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
        Schema::dropIfExists('community_posts');
    }
}
