<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHistoryContactUserAdviceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('history_contact_user_advice', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_advice_id')->unsigned()->index();
            $table->foreign('user_advice_id')->references('id')->on('user_advice')->onDelete('cascade');
            $table->unsignedBigInteger('employee_id')->unsigned()->index();
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->longText('note')->nullable();
            $table->integer("status")->default(0)->nullable();
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
        Schema::dropIfExists('history_contact_user_advice');
    }
}
