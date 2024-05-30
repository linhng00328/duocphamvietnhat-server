<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateListCartsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('list_carts', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('store_id')->unsigned()->index();
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');

            $table->unsignedBigInteger('branch_id')->unsigned()->index();
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');

            $table->string("name")->nullable();

            $table->integer("customer_id")->nullable();
            $table->string("code_voucher")->nullable();
            $table->boolean("is_use_points")->nullable();
            $table->boolean("is_use_balance_collaborator")->nullable();
            $table->boolean("is_use_balance_agency")->nullable();

            $table->integer("payment_method_id")->nullable();
            $table->integer("partner_shipper_id")->nullable();
            $table->integer("shipper_type")->nullable();
            $table->double("total_shipping_fee")->nullable();
            $table->double("ship_discount_amount")->nullable();
            $table->double("discount")->default(0)->nullable();
            $table->integer("customer_address_id")->nullable();
            $table->longText("customer_note")->nullable();

            $table->string("customer_phone")->nullable();
            $table->string("customer_name")->nullable();
            $table->string("customer_email")->nullable();
            $table->integer("customer_sex")->default(0)->nullable();

            $table->timestamp("customer_date_of_birth")->nullable();

            $table->string("edit_order_code")->nullable();
            $table->double("points_total_used_edit_order")->default(0)->nullable();
            $table->double("points_amount_used_edit_order")->default(0)->nullable();

            $table->string("address_detail")->nullable();

            $table->integer("province")->nullable();
            $table->integer("district")->nullable();
            $table->integer("wards")->nullable();

            $table->boolean("is_default")->nullable();
            $table->bigInteger('user_id')->nullable();
            $table->bigInteger('staff_id')->nullable();

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
        Schema::dropIfExists('list_carts');
    }
}
