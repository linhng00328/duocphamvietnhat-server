<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();;
            $table->string('store_code')->unique();
            $table->string('address')->nullable();
            $table->dateTime('date_expried')->nullable();

            $table->string('logo_url')->nullable();
            $table->boolean("has_upload_store")->default(false)->nullable();;
            $table->string('link_google_play')->nullable();
            $table->string('link_apple_store')->nullable();

            $table->unsignedBigInteger('user_id')->unsigned()->index();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->string('version_ios')->nullable();
            $table->string('version_android')->nullable();

            $table->string('store_code_fake_for_ios')->nullable();
            $table->string('store_code_fake_for_zalo_mini')->nullable();

            $table->integer('id_type_of_store')->nullable();
            $table->integer('career')->nullable();
            $table->boolean("is_block_app")->default(false)->nullable();

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
        Schema::dropIfExists('stores');
    }
}
