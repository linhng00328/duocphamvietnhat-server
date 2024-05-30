<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTrainLessonsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('train_lessons', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('store_id')->unsigned()->index();
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');
            
            $table->unsignedBigInteger('train_chapter_id')->unsigned()->index();
            $table->foreign('train_chapter_id')->references('id')->on('train_chapters')->onDelete('cascade');

            $table->string("title")->nullable();
            $table->longText("short_description")->nullable();
            $table->longText("description")->nullable();
            $table->string("link_video_youtube")->nullable();
            $table->integer("position")->default(0)->nullable(); // sx
            
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
        Schema::dropIfExists('train_lessons');
    }
}
