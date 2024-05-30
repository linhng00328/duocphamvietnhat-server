<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCalendarShiftsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('calendar_shifts', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('store_id')->unsigned()->index();
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');

            $table->unsignedBigInteger('branch_id')->unsigned()->index();
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');

            $table->unsignedBigInteger('shift_id')->unsigned()->index();
            $table->foreign('shift_id')->references('id')->on('shifts')->onDelete('cascade');

            $table->boolean("is_add")->default(1)->nullable(); //1 là thêm
            $table->boolean("is_put_a_lot")->default(true)->nullable(); //Thêm nhiều

            $table->timestamp("start_time")->nullable();
            $table->timestamp("end_time")->nullable();

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
        Schema::dropIfExists('calendar_shifts');
    }
}
