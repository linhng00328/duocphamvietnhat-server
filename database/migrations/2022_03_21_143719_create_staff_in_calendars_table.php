<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStaffInCalendarsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('staff_in_calendars', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('store_id')->unsigned()->index();
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');

            $table->unsignedBigInteger('branch_id')->unsigned()->index();
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');

            $table->unsignedBigInteger('shift_id')->unsigned()->index();
            $table->foreign('shift_id')->references('id')->on('shifts')->onDelete('cascade');

            $table->unsignedBigInteger('calendar_shift_id')->unsigned()->index();
            $table->foreign('calendar_shift_id')->references('id')->on('calendar_shifts')->onDelete('cascade');

            $table->unsignedBigInteger('staff_id')->unsigned()->index();
            $table->foreign('staff_id')->references('id')->on('staff')->onDelete('cascade');

            $table->boolean("is_add")->default(1)->nullable(); //1 là thêm

            $table->timestamp("start_time")->nullable();
            $table->timestamp("end_time")->nullable();

            $table->boolean("is_put_a_lot")->default(true)->nullable(); //Thêm nhiều

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
        Schema::dropIfExists('staff_in_calendars');
    }
}
