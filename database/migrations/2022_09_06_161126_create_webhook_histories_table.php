<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWebhookHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('webhook_histories', function (Blueprint $table) {
            $table->id();

            $table->string("order_code")->nullable();
            $table->longText('json')->nullable();
            $table->integer("order_status")->nullable();
            $table->integer("has_updated")->default(0)->nullable();
            $table->integer("type")->nullable();


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
        Schema::dropIfExists('webhook_histories');
    }
}
