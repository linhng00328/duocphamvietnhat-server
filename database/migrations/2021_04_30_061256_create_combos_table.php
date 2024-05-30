<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCombosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('combos', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('store_id')->unsigned()->index();
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');

            $table->boolean("is_end")->nullable();

            $table->string("name")->nullable();
            $table->longText("description")->nullable();
            $table->string("image_url")->nullable();

            $table->timestamp("start_time")->nullable();
            $table->timestamp("end_time")->nullable();

            $table->integer("discount_type")->nullable(); //0 gia co dinh - 1 theo %
            $table->double("value_discount")->nullable();

            $table->boolean("set_limit_amount")->nullable();
            $table->integer("amount")->nullable();
            $table->integer("used")->nullable();

            $table->integer("group_customer")->nullable();
            $table->integer("agency_type_id")->nullable();
            $table->string("agency_type_name")->nullable();

            $table->integer("group_type_id")->nullable();
            $table->string("group_type_name")->nullable();

            $table->longText("group_customers")->nullable();
            $table->longText("group_types")->nullable();
            $table->longText("agency_types")->nullable();

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
        Schema::dropIfExists('combos');
    }
}
