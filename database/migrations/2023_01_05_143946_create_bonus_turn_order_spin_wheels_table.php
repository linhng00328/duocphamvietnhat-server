<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBonusTurnOrderSpinWheelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bonus_turn_order_spin_wheels', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('store_id')->unsigned()->index();
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');
            $table->unsignedBigInteger('spin_wheel_id')->unsigned()->index();
            $table->foreign('spin_wheel_id')->references('id')->on('spin_wheels')->onDelete('cascade');
            $table->integer('bonus_turn')->nullable()->default(0); //
            $table->double('limit_money')->nullable()->default(0); //

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
        Schema::dropIfExists('bonus_turn_order_spin_wheels');
    }
}
