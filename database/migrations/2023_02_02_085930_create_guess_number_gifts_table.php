<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGuessNumberGiftsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('guess_number_gifts', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('store_id')->unsigned()->index();
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');
            $table->unsignedBigInteger('guess_number_id')->unsigned()->index();
            $table->foreign('guess_number_id')->references('id')->on('guess_numbers')->onDelete('cascade');

            $table->string('name')->nullable(); // tên quà
            $table->string('value_gift')->nullable(); // giá trị quà
            $table->integer('amount')->nullable(); // giá trị quà
            $table->longText('image_url')->nullable(); // ảnh qùa
            $table->longText('description')->nullable(); // mô tả quà
            $table->integer('idx_result')->nullable(); // đánh thứ tự quà

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
        Schema::dropIfExists('guess_number_gifts');
    }
}
