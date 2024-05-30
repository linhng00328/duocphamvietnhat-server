<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAgencyTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('agency_types', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('store_id')->unsigned()->index();
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');

            $table->string('name')->nullable();

            $table->integer("position")->nullable();
            $table->double("commission_percent")->default(0)->nullable();
            //$table->double("threshold")->default(-1)->nullable(); //Ngưỡng để đạt
            $table->integer("level")->nullable();
            
            $table->double("auto_set_value_import")->default(0)->nullable();
            $table->double("auto_set_value_share")->default(0)->nullable();

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
        Schema::dropIfExists('agency_types');
    }
}
