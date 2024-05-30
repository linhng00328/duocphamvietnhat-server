<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStoreAddressesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('store_addresses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('store_id')->unsigned()->index();
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');
            $table->string("name")->nullable();
            $table->string("address_detail")->nullable();
            $table->integer("country")->nullable();
            $table->integer("province")->nullable();
            $table->integer("district")->nullable();
            $table->integer("wards")->nullable();
            $table->integer("village")->nullable();
            $table->string("postcode")->nullable();
            $table->string("email")->nullable();
            $table->string("phone")->nullable();
            $table->boolean("is_default_pickup")->nullable();
            $table->boolean("is_default_return")->nullable();

            $table->integer("branch_id")->nullable();

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
        Schema::dropIfExists('store_addresses');
    }
}
