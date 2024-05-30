<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateDateTimekeepingShiftsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('date_timekeeping_shifts', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('store_id')->unsigned()->index();
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');


            $table->unsignedBigInteger('branch_id')->unsigned()->index();
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');

            $table->unsignedBigInteger('date_timekeeping_id')->unsigned()->index();
            $table->foreign('date_timekeeping_id')->references('id')->on('date_timekeepings')->onDelete('cascade');

            $table->unsignedBigInteger('staff_id')->unsigned()->index();
            $table->foreign('staff_id')->references('id')->on('staff')->onDelete('cascade');

            
            $table->integer("shift_id")->nullable();

            $table->string("name")->nullable();
            $table->string("code")->nullable();

            $table->integer("start_work_hour")->nullable();
            $table->integer("start_work_minute")->nullable();
            $table->integer("end_work_hour")->nullable();
            $table->integer("end_work_minute")->nullable();

            $table->integer("start_break_hour")->nullable();
            $table->integer("start_break_minute")->nullable();
            $table->integer("end_break_hour")->nullable();
            $table->integer("end_break_minute")->nullable();

            $table->integer("minutes_late_allow")->default(0)->nullable(); // phút cho phép đi trễ
            $table->integer("minutes_early_leave_allow")->default(0)->nullable(); //phút cho phép về sớm
            $table->string("days_of_week")->nullable(); // 2,3,4,5,6,7,8

            $table->timestamp("updated_time_last")->nullable();
            $table->timestamp('date')->default(DB::raw('NOW()'));


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
        Schema::dropIfExists('date_timekeeping_shifts');
    }
}
