<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHistoryPayImportStocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('history_pay_import_stocks', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('store_id')->unsigned()->index();
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');

            $table->unsignedBigInteger('branch_id')->unsigned()->index();
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');

            $table->unsignedBigInteger('import_stock_id')->unsigned()->index();
            $table->foreign('import_stock_id')->references('id')->on('import_stocks')->onDelete('cascade');

            $table->double("money")->default(0)->nullable();
            $table->double("remaining_amount")->default(0)->nullable();

            
            $table->integer("payment_method")->default(0)->nullable();

            $table->integer("revenue_expenditure_id")->nullable(); //Phiếu chi tạo để thanh toán
            
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
        Schema::dropIfExists('history_pay_import_stocks');
    }
}
