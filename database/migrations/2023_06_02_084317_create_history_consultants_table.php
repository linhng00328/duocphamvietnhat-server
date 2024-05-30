<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHistoryConsultantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('history_consultants', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_advice_id')->unsigned()->index();
            $table->foreign('user_advice_id')->references('id')->on('user_advice')->onDelete('cascade');
            $table->integer('status')->nullable();
            $table->string('content')->nullable();
            $table->timestamp('time_consultant')->nullable();

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
        Schema::dropIfExists('history_consultants');
    }
}
