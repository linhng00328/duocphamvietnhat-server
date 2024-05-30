<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHistoryBonusCollaboratorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('history_bonus_collaborators', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('store_id')->unsigned()->index();
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');

            $table->unsignedBigInteger('collaborator_id')->unsigned()->index();
            $table->foreign('collaborator_id')->references('id')->on('collaborators')->onDelete('cascade');

            $table->double("money_bonus_rewarded")->default(0)->nullable();
            $table->integer("year")->nullable();
            $table->integer("month")->nullable();

            $table->double("limit")->default(0)->nullable();  

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
        Schema::dropIfExists('history_bonus_collaborators');
    }
}
