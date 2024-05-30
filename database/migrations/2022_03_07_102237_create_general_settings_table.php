<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGeneralSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('general_settings', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('store_id')->unsigned()->index();
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');


            $table->boolean("noti_near_out_stock")->default(false)->nullable();
            $table->integer("noti_stock_count_near")->default(10)->nullable();
            $table->boolean("allow_semi_negative")->default(true)->nullable();
            $table->string("email_send_to_customer")->nullable();
            $table->boolean("enable_vat")->default(0)->nullable();
            $table->double("percent_vat")->default(10)->nullable();
            $table->boolean("allow_branch_payment_order")->default(0)->nullable();
            $table->boolean("auto_choose_default_branch_payment_order")->default(1)->nullable();
            $table->boolean("required_agency_ctv_has_referral_code")->default(0)->nullable();
            $table->boolean("is_default_terms_agency_collaborator")->default(1)->nullable();
            $table->string("terms_agency")->nullable();
            $table->string("terms_collaborator")->nullable();

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
        Schema::dropIfExists('general_settings');
    }
}
