<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePublicApiSessionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('public_api_sessions', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('store_id')->index();
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');

            $table->string('token');
            $table->string('refresh_token');
            $table->dateTime('token_expried');
            $table->dateTime('refresh_token_expried');
            $table->boolean('enable')->default(0);
            $table->string('webhook_url')->nullable();
            $table->boolean('enable_webhook')->default(0);

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
        Schema::dropIfExists('public_api_sessions');
    }
}
