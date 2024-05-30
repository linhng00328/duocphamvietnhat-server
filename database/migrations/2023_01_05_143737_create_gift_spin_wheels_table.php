<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGiftSpinWheelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gift_spin_wheels', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('store_id')->unsigned()->index();
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');
            $table->unsignedBigInteger('spin_wheel_id')->unsigned()->index();
            $table->foreign('spin_wheel_id')->references('id')->on('spin_wheels')->onDelete('cascade');
            $table->bigInteger('user_id')->nullable();
            $table->string('name')->nullable();
            $table->string('image_url')->nullable();
            $table->integer('type_gift')->nullable()->default(0);
            $table->double('amount_coin')->nullable()->default(0);
            $table->double('percent_received')->nullable()->default(0);
            $table->integer('amount_gift')->nullable()->default(0);
            $table->string('value_gift')->nullable();
            $table->string('text')->nullable();
            $table->tinyInteger('is_lost_turn')->nullable();

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
        Schema::dropIfExists('gift_spin_wheels');
    }
}
