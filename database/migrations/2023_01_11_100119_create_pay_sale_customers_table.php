<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaySaleCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pay_sale_customers', function (Blueprint $table) {
            $table->id();


            $table->unsignedBigInteger('store_id')->unsigned()->index();
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');

            $table->unsignedBigInteger('sale_customer_id')->unsigned()->index();
            $table->foreign('sale_customer_id')->references('id')->on('sale_customers')->onDelete('cascade');

            $table->double("money")->default(0)->nullable();
            $table->integer("status")->default(0)->nullable(); //0 chờ xử lý - 1 hoàn lại - 2 đã thanh toán
            $table->integer("from")->default(0)->nullable(); //0 yêu cầu từ CTV - 1 Do user lên danh sách


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
        Schema::dropIfExists('pay_sale_customers');
    }
}
