<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHistoryGiftGuessNumbersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('history_gift_guess_numbers', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('store_id')->unsigned()->index();
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');
            $table->unsignedBigInteger('guess_number_id')->unsigned()->index();
            $table->foreign('guess_number_id')->references('id')->on('guess_numbers')->onDelete('cascade');
            $table->unsignedBigInteger('player_guess_number_id')->unsigned()->index();
            $table->foreign('player_guess_number_id')->references('id')->on('player_guess_numbers')->onDelete('cascade');
            $table->bigInteger('guess_number_result_id')->nullable();
            $table->string('value_predict')->nullable();
            $table->tinyInteger('is_correct')->nullable(); // đáp án đúng
            $table->string('note')->nullable();

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
        Schema::dropIfExists('history_gift_guess_numbers');
    }
}
