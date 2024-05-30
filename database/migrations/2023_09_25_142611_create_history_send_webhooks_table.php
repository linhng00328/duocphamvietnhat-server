<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHistorySendWebhooksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('history_send_webhooks', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('store_id')->index();
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');

            $table->string('type')->nullable();
            $table->string('webhook_url')->nullable();
            $table->longText('json_data_send')->nullable();
            $table->longText('json_data_success')->nullable();
            $table->longText('json_data_fail')->nullable();
            $table->string('status')->nullable();

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
        Schema::dropIfExists('history_send_webhooks');
    }
}
