<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOtpUnitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('otp_units', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('store_id')->unsigned()->index();
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');
            $table->string("sender")->nullable();
            $table->longText("token")->nullable();
            $table->longText("content")->nullable();
            $table->longText("image_url")->nullable();
            $table->string("partner")->nullable();
            $table->boolean("is_use")->default(false)->nullable();
            $table->boolean("is_default")->default(false)->nullable();

            $table->longText('content_order')->nullable();
            $table->boolean('is_order')->default(false)->nullable();

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
        Schema::dropIfExists('otp_units');
    }
}
