<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateDateTimekeepingHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('date_timekeeping_histories', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('store_id')->unsigned()->index();
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');

            $table->unsignedBigInteger('branch_id')->unsigned()->index();
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');

            $table->unsignedBigInteger('staff_id')->unsigned()->index();
            $table->foreign('staff_id')->references('id')->on('staff')->onDelete('cascade');

            $table->unsignedBigInteger('date_timekeeping_id')->unsigned()->index();
            $table->foreign('date_timekeeping_id')->references('id')->on('date_timekeepings')->onDelete('cascade');

            $table->timestamp("time_check")->nullable();
            $table->boolean("is_checkin")->default(true)->nullable();
            $table->boolean("remote_timekeeping")->default(false)->nullable();
            $table->boolean("from_user")->default(false)->nullable();
            
            $table->integer("from_user_id")->nullable();
            $table->integer("from_staff_id")->nullable();

            $table->boolean("is_bonus")->default(true)->nullable();

            $table->integer("checkout_for_checkin_id")->nullable();

            $table->integer("status")->default(0)->nullable(); //0 ok, 1 cho duyet, 2 da duyet, 3 huy
            $table->longText("note")->nullable();
            $table->timestamp('date')->default(DB::raw('NOW()'));
            $table->longText("reason")->nullable();

            $table->string("wifi_name")->nullable();
            $table->string("wifi_mac")->nullable();


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
        Schema::dropIfExists('date_timekeeping_histories');
    }
}
