<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTaskNotiAdminsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('task_noti_admins', function (Blueprint $table) {
            $table->id();

            $table->string("title")->nullable();
            $table->string("description")->nullable();

            $table->integer("group_user")->default(0)->nullable(); //0 tất cả , 1 sinh nhật
            $table->time('time_of_day')->default('00:00')->nullable(); //hàng ngày, hàng tuần, hàng tháng
            
            $table->integer('type_schedule')->nullable(); //0 chạy đúng 1 lần, 1 hàng ngày, 2 hàng tuần, 3 hàng tháng
            $table->timestamp('time_run')->nullable();  //chạy đúng 1 lần
            $table->integer('day_of_week')->nullable(); //hàng tuần
            $table->integer('day_of_month')->nullable(); //hàng tháng

            $table->timestamp('time_run_near')->nullable(); 
            
            $table->integer('status')->nullable(); //0 đang chạy, 1 tạm dừng, 2 đã xong

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
        Schema::dropIfExists('task_noti_admins');
    }
}
