<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHistoryGiftSpinWheelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('history_gift_spin_wheels', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('store_id')->unsigned()->index();
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');
            $table->unsignedBigInteger('player_spin_wheel_id')->unsigned()->index();
            $table->foreign('player_spin_wheel_id')->references('id')->on('player_spin_wheels')->onDelete('cascade');
            $table->double('amount_coin_current')->nullable()->default(0);
            $table->string('name_gift')->nullable();
            $table->string('image_url_gift')->nullable();
            $table->integer('amount_gift')->nullable()->default(0);
            $table->integer('type_gift')->nullable()->default(0);
            $table->double('amount_coin_change')->nullable()->default(0);
            $table->double('amount_coin_changed')->nullable()->default(0);
            $table->string('value_gift')->nullable();
            $table->string('text')->nullable();

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
        Schema::dropIfExists('history_gift_spin_wheels');
    }
}
