<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHistorySmsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('history_sms', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('store_id')->index()->nullable();
            $table->string("phone")->nullable();
            $table->string("ip")->nullable();
            $table->string('partner')->nullable();
            $table->timestamp("time_generate")->nullable();
            $table->longText("content")->nullable();
            $table->integer('type')->nullable()->default(0);

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
        Schema::dropIfExists('history_sms');
    }
}
