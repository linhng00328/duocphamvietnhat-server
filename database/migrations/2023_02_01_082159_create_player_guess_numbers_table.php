<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlayerGuessNumbersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('player_guess_numbers', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('store_id')->unsigned()->index();
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');
            $table->unsignedBigInteger('guess_number_id')->unsigned()->index();
            $table->foreign('guess_number_id')->references('id')->on('guess_numbers')->onDelete('cascade');
            $table->unsignedBigInteger('customer_id')->unsigned()->index();
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->integer('total_turn_play')->nullable()->default(0);
            $table->double('total_win')->nullable()->default(0);
            $table->integer('total_missed')->nullable()->default(0);
            $table->tinyInteger('is_get_turn_today')->nullable()->default(0);
            $table->string('check_get_turn')->nullable();

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
        Schema::dropIfExists('player_guess_numbers');
    }
}
