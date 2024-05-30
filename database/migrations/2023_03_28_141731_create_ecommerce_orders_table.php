<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEcommerceOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ecommerce_orders', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('store_id')->unsigned()->index();
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');


            $table->string('phone_number')->nullable();

            $table->string("order_id_in_ecommerce")->nullable();
            $table->string("order_code")->nullable();

            $table->string("order_status")->nullable();
            $table->string("payment_status")->nullable();

            $table->double("ship_discount_amount")->default('0')->nullable();
            $table->double("total_shipping_fee")->nullable();

            $table->double("total_before_discount")->default('0')->nullable();
            $table->double("total_after_discount")->default('0')->nullable();

            $table->double("discount")->default('0')->nullable(); //Chiết khấu
            $table->double("total_final")->default('0')->nullable();

            $table->double("total_cost_of_capital")->default('0')->nullable();

            $table->double("remaining_amount")->default(0)->nullable();

            $table->integer("branch_id")->nullable();

            $table->longText("line_items_in_time")->nullable();


            $table->string("customer_province_name")->nullable();
            $table->string("customer_district_name")->nullable();
            $table->string("customer_wards_name")->nullable();

            $table->string("customer_name")->nullable();
            $table->integer("customer_country")->nullable();
            $table->integer("customer_province")->nullable();
            $table->integer("customer_district")->nullable();
            $table->integer("customer_wards")->nullable();
            $table->integer("customer_village")->nullable();
            $table->string("customer_postcode")->nullable();
            $table->string("customer_email")->nullable();
            $table->string("customer_phone")->nullable();
            $table->string("customer_address_detail")->nullable();

            $table->string("customer_note")->nullable();

            $table->integer("created_by_user_id")->nullable();
            $table->integer("created_by_staff_id")->nullable();

            $table->string("order_code_refund")->nullable();
            $table->integer("order_from")->default(0)->nullable();

            $table->timestamp('last_time_change_order_status')->nullable();

            $table->timestamp('created_at_ecommerce')->nullable();

            $table->double("package_weight")->nullable();
            $table->double("package_length")->nullable();
            $table->double("package_width")->nullable();
            $table->double("package_height")->nullable();

            $table->string("from_platform")->nullable();
            $table->string("shop_id")->nullable();
            $table->string("shop_name")->nullable();
            $table->string("code")->nullable();


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
        Schema::dropIfExists('ecommerce_orders');
    }
}
