<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBranchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('branches', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('store_id')->unsigned()->index();
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');
            $table->string("name")->nullable();
            $table->string("address_detail")->nullable();

            $table->integer("province")->nullable();
            $table->integer("district")->nullable();
            $table->integer("wards")->nullable();

            $table->string("province_name")->nullable();
            $table->string("district_name")->nullable();
            $table->string("wards_name")->nullable();

            $table->string("branch_code")->nullable();
            $table->string("postcode")->nullable();
            $table->string("email")->nullable();
            $table->string("phone")->nullable();
            $table->boolean("is_default")->nullable();
            $table->boolean("is_default_order_online")->default(false)->nullable();
            
            
            $table->string("txt_code")->nullable();
            $table->string("account_number")->nullable();
            $table->string("account_name")->nullable();
            $table->string("bank")->nullable();


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
        Schema::dropIfExists('branches');
    }
}
