<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTallySheetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tally_sheets', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('store_id')->unsigned()->index();
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');

            $table->unsignedBigInteger('branch_id')->unsigned()->index();
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');

            $table->string("code")->unique();

            $table->integer("existing_branch")->default(0)->nullable();
            $table->integer("reality_exist")->default(0)->nullable();
            $table->integer("deviant")->default(0)->nullable();

            $table->integer("status")->default(0)->nullable();
            $table->longText("note")->nullable();
            $table->timestamp("balance_time")->nullable();

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
        Schema::dropIfExists('tally_sheets');
    }
}
