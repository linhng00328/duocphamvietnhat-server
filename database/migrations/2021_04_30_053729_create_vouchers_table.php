<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVouchersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('store_id')->unsigned()->index();
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');

            $table->boolean("is_end")->nullable();

            $table->integer("voucher_type")->nullable(); //0 All - 1 Mot So sp

            $table->string("name")->nullable();
            $table->string("code")->nullable();
            $table->longText("description")->nullable();
            $table->string("image_url")->nullable();
            $table->timestamp("start_time")->nullable();
            $table->timestamp("end_time")->nullable();

            $table->integer("discount_for")->default(0)->nullable();  // 0 trừ vào đơn hàng,1 trừ phí ship

            $table->boolean("is_free_ship")->default(false)->nullable(); //dành cho discount_for == 1
            $table->double("ship_discount_value")->default(0)->nullable(); //dành cho discount_for == 1

            $table->integer("discount_type")->nullable(); //0 gia co dinh - 1 theo %
            $table->double("value_discount")->nullable();
            $table->boolean("set_limit_value_discount")->nullable(); //if percent
            $table->double("max_value_discount")->nullable(); //if percent


            $table->boolean("set_limit_total")->nullable();
            $table->double("value_limit_total")->nullable();


            $table->boolean("is_show_voucher")->nullable();

            $table->boolean("set_limit_amount")->nullable();
            $table->integer("amount")->nullable();
            $table->integer("used")->nullable();

            $table->integer("group_customer")->nullable();
            $table->integer("agency_type_id")->nullable();
            $table->string("agency_type_name")->nullable();

            $table->integer("group_type_id")->nullable();
            $table->string("group_type_name")->nullable();

            $table->boolean("is_public")->default(true)->nullable(); // ẩn/hiện voucher cho khách
            $table->boolean("is_use_once")->default(false)->nullable(); // mỗi khách chỉ được sử dụng một hay nhiều lần voucher
            $table->boolean("is_use_once_code_multiple_time")->default(true)->nullable(); // true: Một mã dùng cho nhiều lần false: Nhiều mã chỉ sử dụng một lần
            $table->integer("amount_use_once")->default(0)->nullable(); // áp dụng khi sử dụng option nhiều mã chỉ sử dụng một lần
            $table->longText("group_customers")->nullable();
            $table->longText("group_types")->nullable();
            $table->longText("agency_types")->nullable();

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
        Schema::dropIfExists('vouchers');
    }
}
