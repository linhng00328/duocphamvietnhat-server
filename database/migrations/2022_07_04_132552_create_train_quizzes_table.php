<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTrainQuizzesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('train_quizzes', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('store_id')->unsigned()->index();
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');

            $table->unsignedBigInteger('train_course_id')->unsigned()->index();
            $table->foreign('train_course_id')->references('id')->on('train_courses')->onDelete('cascade');

            $table->string("title")->nullable();
            $table->longText("short_description")->nullable();

            $table->integer("minute")->default(0)->nullable(); //so phut
            $table->boolean('show')->default(1)->nullable();

            $table->boolean('auto_change_order_questions')->default(0)->nullable();
            $table->boolean('auto_change_order_answer')->default(0)->nullable();

            $table->integer("count_answer_right_complete")->default(0)->nullable(); // số câu trả đúng là hoàn thành bài thi



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
        Schema::dropIfExists('train_quizzes');
    }
}
