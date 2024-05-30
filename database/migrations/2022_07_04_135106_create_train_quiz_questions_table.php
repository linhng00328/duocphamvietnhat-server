<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTrainQuizQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('train_quiz_questions', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('store_id')->unsigned()->index();
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');


            $table->unsignedBigInteger('quiz_id')->unsigned()->index();
            $table->foreign('quiz_id')->references('id')->on('train_quizzes')->onDelete('cascade');

            $table->longText("question")->nullable();
            $table->longText("question_image")->nullable();

            $table->longText("answer_a")->nullable();
            $table->longText("answer_b")->nullable();
            $table->longText("answer_c")->nullable();
            $table->longText("answer_d")->nullable();
            $table->string("right_answer")->nullable();


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
        Schema::dropIfExists('train_quiz_questions');
    }
}
