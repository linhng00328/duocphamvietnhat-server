<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHistoryChangeLevelAgenciesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('history_change_level_agencies', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('store_id')->unsigned()->index();
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');

            $table->unsignedBigInteger('agency_id')->unsigned()->index();
            $table->foreign('agency_id')->references('id')->on('agencies')->onDelete('cascade');

            $table->string("last_agency_type_name")->nullable();
            $table->string("new_agency_type_name")->nullable();

            $table->integer("last_agency_type_id")->nullable();
            $table->integer("new_agency_type_id")->nullable();

            $table->double("current_share_agency")->default('0')->nullable();
            $table->double("current_total_after_discount_no_use_bonus")->default('0')->nullable();

            $table->double("auto_set_value_share")->default('0')->nullable();
            $table->double("auto_set_value_import")->default('0')->nullable();

            $table->timestamp('date_from')->nullable();
            $table->timestamp('date_to')->nullable();

            $table->integer("action_from")->default('0')->nullable(); //0 thủ công, 1 tự động

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
        Schema::dropIfExists('history_change_level_agencies');
    }
}
