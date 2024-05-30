<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateImportStocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('import_stocks', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('store_id')->unsigned()->index();
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');

            $table->unsignedBigInteger('branch_id')->unsigned()->index();
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');

            $table->integer("supplier_id")->nullable();

            $table->string("code")->unique();

            $table->integer("existing_branch")->default(0)->nullable();

            $table->integer("status")->default(0)->nullable();
            $table->integer("payment_status")->default(0)->nullable();
            $table->longText("note")->nullable();

            $table->integer("payment_method")->default(0)->nullable();
            $table->double("remaining_amount")->default(0)->nullable(); // số tiền cần thanh toán còn lại
            $table->integer("total_number")->default(0)->nullable();
            $table->double("total_amount")->default(0)->nullable(); //tiền hàng
            $table->double("total_final")->default(0)->nullable(); //tổng tất cả
            $table->double("discount")->default(0)->nullable();
            $table->double("tax")->default(0)->nullable();
            $table->double("cost")->default(0)->nullable(); //chi phí nhập hàng

            $table->bigInteger('user_id')->nullable();
            $table->bigInteger('staff_id')->nullable();

            $table->double("refund_received_money")->default(0)->nullable(); //Số tiền nhận hoàn từ ncc
            $table->string("import_stock_code_refund")->nullable();
            $table->bigInteger('import_stock_id_refund')->nullable();

            $table->boolean('has_refunded')->nullable();

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
        Schema::dropIfExists('import_stocks');
    }
}
