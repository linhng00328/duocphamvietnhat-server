<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('store_id')->unsigned()->index();
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');

            $table->bigInteger('customer_id')->nullable();
            $table->boolean('logged')->default('1')->nullable();
            $table->string('phone_number')->nullable();

            $table->string("order_code")->unique();

            $table->integer("order_status")->nullable();
            $table->integer("payment_status")->nullable();

            $table->integer("payment_method_id")->nullable();
            $table->integer("payment_partner_id")->nullable();

            $table->integer("partner_shipper_id")->nullable();
            $table->integer("shipper_type")->nullable();

            $table->double("ship_discount_amount")->default('0')->nullable();
            $table->double("total_shipping_fee")->nullable();
            $table->double("vat")->default(0)->nullable();
            $table->double("cod")->default(0)->nullable();

            $table->double("balance_agency_used")->default('0')->nullable();
            $table->double("balance_collaborator_used")->default('0')->nullable();
            $table->double("bonus_points_amount_used")->default('0')->nullable();
            $table->double("total_before_discount")->default('0')->nullable();
            $table->double("combo_discount_amount")->default('0')->nullable();
            $table->double("product_discount_amount")->default('0')->nullable();
            $table->double("voucher_discount_amount")->default('0')->nullable();
            $table->double("total_after_discount")->default('0')->nullable();

            $table->double("discount")->default('0')->nullable(); //Chiết khấu
            $table->double("total_final")->default('0')->nullable();
            $table->double("total_money_refund")->default('0')->nullable();

            $table->double("total_cost_of_capital")->default('0')->nullable();

            $table->double("remaining_amount")->default(0)->nullable();

            $table->integer("branch_id")->nullable();

            $table->longText("used_discount")->nullable();
            $table->longText("used_combos")->nullable();
            $table->longText("used_voucher")->nullable();

            $table->longText("line_items_in_time")->nullable();
            $table->boolean('allow_rose_referral_customer')->default('0')->nullable();


            $table->boolean('reviewed')->default('0')->nullable();

            $table->integer("points_awarded_to_customer")->default(0)->nullable();
            $table->integer("point_for_agency")->default(0)->nullable();
            $table->integer("used_bonus_products")->default(0)->nullable();

            $table->boolean("is_use_points")->default(0)->nullable();
            $table->boolean("is_order_for_customer")->default(0)->nullable();

            $table->integer("total_points_used")->default(0)->nullable();

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

            $table->string("customer_province_name")->nullable();
            $table->string("customer_district_name")->nullable();
            $table->string("customer_wards_name")->nullable();

            $table->integer("collaborator_by_customer_id")->nullable();
            $table->integer("agency_by_customer_id")->nullable();
            $table->integer("agency_ctv_by_customer_id")->nullable();
            $table->integer("agency_ctv_by_customer_referral_id")->nullable();

            $table->integer("sale_by_staff_id")->nullable();



            $table->double("share_agency_referen")->nullable();
            $table->double("share_agency")->nullable();
            $table->double("share_collaborator_referen")->nullable();
            $table->double("share_collaborator")->nullable();
            $table->boolean("is_handled_balance_collaborator")->nullable(); //Đã cộng tiền cho ctv chưa
            $table->boolean("is_handled_balance_agency")->nullable(); //Đã cộng tiền cho agency chưa

            $table->integer("created_by_user_id")->nullable();
            $table->integer("created_by_staff_id")->nullable();

            $table->boolean("from_pos")->default(0)->nullable();

            $table->string("order_code_refund")->nullable();
            $table->integer("order_from")->default(0)->nullable();

            $table->boolean("has_refund_money_for_ctv")->default(0)->nullable();
            $table->boolean("has_refund_money_for_agency")->default(0)->nullable();

            $table->boolean("has_refund_point_for_customer")->default(0)->nullable();

            $table->timestamp('last_time_change_order_status')->nullable();

            $table->double("package_weight")->nullable();
            $table->double("package_length")->nullable();
            $table->double("package_width")->nullable();
            $table->double("package_height")->nullable();

            $table->string("ship_speed_code")->nullable();
            $table->string("description_shipper")->nullable();


            $table->timestamp('completed_at')->nullable();
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
        Schema::dropIfExists('orders');
    }
}
