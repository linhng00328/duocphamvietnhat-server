<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGuessNumberResultsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('guess_number_results', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('store_id')->unsigned()->index();
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');
            $table->unsignedBigInteger('guess_number_id')->unsigned()->index();
            $table->foreign('guess_number_id')->references('id')->on('guess_numbers')->onDelete('cascade');

            $table->string('text_result')->nullable(); // kết quả 
            $table->tinyInteger('is_correct')->nullable(); // đáp án đúng
            $table->longText('image_url_result')->nullable(); // ảnh qùa
            $table->longText('description_result')->nullable(); // mô tả quà

            $table->string('value_gift')->nullable(); // giá trị quà
            $table->longText('image_url_gift')->nullable(); // ảnh qùa
            $table->longText('description_gift')->nullable(); // mô tả quà

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
        Schema::dropIfExists('guess_number_results');
    }
}
