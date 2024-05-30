<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGroupCustomerConditionItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('group_customer_condition_items', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('store_id')->unsigned()->index();
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');

            $table->unsignedBigInteger('group_customer_id')->unsigned()->index();
            $table->foreign('group_customer_id')->references('id')->on('group_customers')->onDelete('cascade');


            $table->integer("type_compare")->default(0)->nullable(); //0 Tổng mua (Chỉ đơn hoàn thành), 1 tổng bán, 2 Xu hiện tại, 3 Số lần mua hàng 4, tháng sinh nhật 5, tuổi 6, giới tính, 7 tỉnh, 8 ngày đăng ký
            $table->string("comparison_expression")->default(0)->nullable(); //Biểu thức so sánh
            $table->string("value_compare")->default("")->nullable(); //Giá trị so sánh

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
        Schema::dropIfExists('group_customer_condition_items');
    }
}
