<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConfigShipsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('config_ships', function (Blueprint $table) {

            $table->id();

            $table->unsignedBigInteger('store_id')->unsigned()->index();
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');


            $table->boolean("is_calculate_ship")->default(false)->nullable();
            $table->boolean("use_fee_from_partnership")->default(false)->nullable(); //true la tu nha giao hang, false tu thiet dinh
            $table->boolean("use_fee_from_default")->default(false)->nullable(); //su dung phi ship mac dinh
            $table->double("fee_urban")->default(0)->nullable();
            $table->double("fee_suburban")->default(0)->nullable();
            $table->longText("fee_default_description")->nullable();
            $table->longText("urban_list_id_province_json")->nullable();
            $table->longText("urban_list_name_province_json")->nullable();

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
        Schema::dropIfExists('config_ships');
    }
}
