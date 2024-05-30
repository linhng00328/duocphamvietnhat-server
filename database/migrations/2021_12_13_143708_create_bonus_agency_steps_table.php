<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBonusAgencyStepsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bonus_agency_steps', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('store_id')->unsigned()->index();
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');
            
            $table->double("threshold")->default(0)->nullable(); //Ngưỡng để đạt
            $table->string("reward_name")->nullable();
            $table->longText("reward_description")->nullable();
            $table->string("reward_image_url")->nullable();
            $table->double("reward_value")->default(0)->nullable(); //

            $table->integer("limit")->nullable();
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
        Schema::dropIfExists('bonus_agency_steps');
    }
}
