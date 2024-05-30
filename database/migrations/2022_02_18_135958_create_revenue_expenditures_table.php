<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRevenueExpendituresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('revenue_expenditures', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('store_id')->unsigned()->index();
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');

            $table->unsignedBigInteger('branch_id')->unsigned()->index();
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');

            $table->string("code")->nullable();

            $table->integer("action_create")->default(0)->nullable();
            $table->integer("recipient_group")->default(0)->nullable();

            $table->integer("type")->default(0)->nullable();

            $table->integer("recipient_references_id")->nullable();
            $table->integer("payment_method")->nullable();
            $table->integer("references_id")->nullable(); //id kết với phiếu có thể đơn hàng hoặc công nợ
            $table->string("references_value")->nullable();

            $table->string("reference_name")->nullable();
            $table->double("current_money")->default(0)->nullable();
            $table->double("change_money")->default(0)->nullable();

            $table->boolean("allow_accounting")->default(true)->nullable();
            $table->boolean("is_revenue")->default(true)->nullable();

            $table->string("description")->nullable();

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
        Schema::dropIfExists('revenue_expenditures');
    }
}
