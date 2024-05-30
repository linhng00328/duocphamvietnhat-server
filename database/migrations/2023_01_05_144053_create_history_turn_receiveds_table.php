<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHistoryTurnReceivedsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('history_turn_receiveds', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('store_id')->unsigned()->index();
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');
            $table->unsignedBigInteger('player_spin_wheel_id')->unsigned()->index();
            $table->foreign('player_spin_wheel_id')->references('id')->on('player_spin_wheels')->onDelete('cascade');
            $table->bigInteger('customer_id')->nullable();
            $table->integer('amount_turn_current')->nullable()->default(0);
            $table->integer('amount_turn_changed')->nullable()->default(0);
            $table->integer('type_from')->nullable()->default(0);
            $table->string('title')->nullable();
            $table->string('description')->nullable();

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
        Schema::dropIfExists('history_turn_receiveds');
    }
}
