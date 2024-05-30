<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLastSubmitQuizzesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('last_submit_quizzes', function (Blueprint $table) {
            $table->id();


            $table->unsignedBigInteger('store_id')->unsigned()->index();
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');

            $table->unsignedBigInteger('quiz_id')->unsigned()->index();
            $table->foreign('quiz_id')->references('id')->on('train_quizzes')->onDelete('cascade');

            $table->unsignedBigInteger('customer_id')->unsigned()->index()->nullable();
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade')->nullable();

            $table->integer("work_time")->default(0)->nullable(); //so giây

            $table->integer("total_questions")->default(0)->nullable();
            $table->integer("total_correct_answer")->default(0)->nullable();
            $table->integer("total_wrong_answer")->default(0)->nullable();

            $table->longText('history_submit_quizzes_json')->nullable();

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
        Schema::dropIfExists('last_submit_quizzes');
    }
}
