<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateGuessNumbersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('guess_numbers', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('store_id')->unsigned()->index();
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');
            $table->unsignedBigInteger('user_id')->unsigned()->index()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('name')->nullable(); // tên game đoán số
            $table->string('icon')->nullable(); // icon mini game
            $table->longText('images')->nullable(); // ảnh game đoán số
            $table->integer('turn_in_day')->nullable()->default(1); // lượt chơi trong ngày
            $table->timestamp('time_start')->nullable()->default(DB::raw('CURRENT_TIMESTAMP')); // thời gian bắt đầu minigame 
            $table->timestamp('time_end')->nullable()->default(DB::raw('CURRENT_TIMESTAMP')); // thời gian kết thúc minigame 
            $table->timestamp('time_announce_result')->nullable()->default(DB::raw('CURRENT_TIMESTAMP')); // thời gian công bố kết quả 
            $table->integer('status')->nullable()->default(0); // trạng thái game đoán số 0: đang hoạt động, 1 đã kết thúc, 2 đã 
            $table->bigInteger('group_customer_id')->nullable(); // mã nhóm khách hàng
            $table->integer('apply_for')->nullable()->default(0); // áp dụng cho đối tượng nào
            $table->longText('description')->nullable(); // thể lệ cuộc chơi
            $table->longText('note')->nullable(); //
            $table->tinyInteger('is_guess_number')->nullable()->default(1); // có phải là game đoán số
            $table->tinyInteger('is_show_game')->nullable()->default(1); // có hiển thị game
            $table->tinyInteger('is_initial_result')->nullable()->default(1); // có khởi tạo kết quả
            $table->tinyInteger('is_show_all_prizer')->nullable()->default(0); // có hiển thị list người trúng thưởng hay 1

            $table->tinyInteger('is_limit_people')->nullable()->default(1); // có giới hạn số lượng ng tham gia
            $table->integer('number_limit_people')->nullable()->default(0); // số lượng giới hạn
            $table->string('background_image_url')->nullable(); // Ảnh nền trò chơi
            $table->integer('type_background_image')->nullable()->default(0); // Loại ảnh nền

            $table->integer('range_number')->nullable(); // Phạm vi số game dự đoán
            $table->string('text_result')->nullable(); // đáp án game
            $table->string('value_gift')->nullable(); // quà cho ng chơi

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
        Schema::dropIfExists('guess_numbers');
    }
}
