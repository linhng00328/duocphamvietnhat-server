<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateSpinWheelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('spin_wheels', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('store_id')->unsigned()->index();
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');
            $table->unsignedBigInteger('user_id')->unsigned()->index()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('name')->nullable(); // tên vòng quay
            $table->string('icon')->nullable(); // icon mini game
            $table->longText('images')->nullable(); // ảnh vòng quay
            $table->integer('turn_in_day')->nullable()->default(1); // lượt chơi trong ngày
            $table->timestamp('time_start')->nullable()->default(DB::raw('CURRENT_TIMESTAMP')); // thời gian bắt đầu minigame spin wheel
            $table->timestamp('time_end')->nullable()->default(DB::raw('CURRENT_TIMESTAMP')); // thời gian kết thúc minigame spin wheel
            $table->integer('status')->nullable()->default(0); // trạng thái vòng quay 0: đang hoạt động, 1 đã kết thúc, 2 đã 
            $table->tinyInteger('is_shake')->nullable()->default(0); // là lắc
            $table->bigInteger('group_customer_id')->nullable(); // danh sách mã nhóm khách hàng
            $table->bigInteger('agency_id')->nullable(); // mã nhóm khách hàng
            $table->integer('apply_for')->nullable()->default(0); // áp dụng cho đối tượng nào
            $table->longText('description')->nullable(); // thể lệ cuộc chơi
            $table->longText('note')->nullable(); //

            $table->tinyInteger('is_limit_people')->nullable()->default(1); // có giới hạn người chơi hay không
            $table->integer('number_limit_people')->nullable()->default(0); // số lượng giới hạn
            $table->double('max_amount_coin_per_player')->nullable()->default(0); // số lượng tối đa xu tặng cho người chơi
            $table->integer('max_amount_gift_per_player')->nullable()->default(0); // số lượng phần quà tối đa tặng cho người chơi
            $table->string('background_image_url')->nullable(); // Ảnh nền trò chơi
            $table->integer('type_background_image')->nullable()->default(0); // Loại ảnh nền trò chơi

            $table->longText('apply_fors')->nullable(); // áp dụng cho đối tượng nào
            $table->longText('group_types')->nullable(); // áp dụng cho nhóm đối tượng nào
            $table->longText('agency_types')->nullable(); // áp dụng cho đối tượng đại lý nào

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
        Schema::dropIfExists('spin_wheels');
    }
}
