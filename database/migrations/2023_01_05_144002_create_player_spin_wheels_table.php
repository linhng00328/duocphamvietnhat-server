<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlayerSpinWheelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('player_spin_wheels', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('store_id')->unsigned()->index();
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');
            $table->unsignedBigInteger('spin_wheel_id')->unsigned()->index();
            $table->foreign('spin_wheel_id')->references('id')->on('spin_wheels')->onDelete('cascade');
            $table->unsignedBigInteger('customer_id')->unsigned()->index();
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->integer('total_turn_play')->nullable()->default(0);
            $table->double('total_coin_received')->nullable()->default(0);
            $table->integer('total_gift_received')->nullable()->default(0);
            $table->string('check_get_turn')->nullable();
            $table->integer('group_customer_id')->nullable();
            $table->string('icon')->nullable();
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
        Schema::dropIfExists('player_spin_wheels');
    }
}
