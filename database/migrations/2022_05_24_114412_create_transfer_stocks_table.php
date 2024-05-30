<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransferStocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transfer_stocks', function (Blueprint $table) {
            $table->id();

            $table->string("code")->unique();
            
            $table->unsignedBigInteger('store_id')->unsigned()->index();
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');

            $table->unsignedBigInteger('from_branch_id')->unsigned()->index();
            $table->foreign('from_branch_id')->references('id')->on('branches')->onDelete('cascade');

            $table->unsignedBigInteger('to_branch_id')->unsigned()->index();
            $table->foreign('to_branch_id')->references('id')->on('branches')->onDelete('cascade');

            $table->integer("status")->default(0)->nullable();  //0 Đang chờ chuyển //1 đã hủy chuyển //2 đã chuyển

            $table->longText("note")->nullable();

            $table->bigInteger('user_id')->nullable();
            $table->bigInteger('staff_id')->nullable();

            $table->timestamp("handle_time")->nullable();

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
        Schema::dropIfExists('transfer_stocks');
    }
}
