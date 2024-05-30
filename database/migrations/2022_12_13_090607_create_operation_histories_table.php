<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOperationHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('operation_histories', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('store_id')->unsigned()->index();
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');

            $table->string("function_type")->nullable();
            $table->string("action_type")->nullable();

            $table->string("staff_id")->nullable();
            $table->string("staff_name")->nullable();

            $table->string("user_id")->nullable();
            $table->string("user_name")->nullable();

            $table->integer("branch_id")->nullable();
            $table->integer("branch_name")->nullable();

            $table->longText("content")->nullable();

            $table->string("ip")->nullable();

            $table->integer("references_id")->nullable();
            $table->string("references_value")->nullable();

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
        Schema::dropIfExists('operation_histories');
    }
}
