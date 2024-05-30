<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConfigUserVipsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('config_user_vips', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id')->unsigned()->index();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->string('trader_mark_name')->nullable();
            $table->string('url_logo_image')->nullable();
            $table->string('url_logo_small_image')->nullable();
            $table->string('url_login_image')->nullable();
            $table->string('user_copyright')->nullable();
            $table->string('customer_copyright')->nullable();
            $table->string('url_customer_copyright')->nullable();
            $table->longText('list_json_id_theme_vip')->nullable();// [1,2,3]

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
        Schema::dropIfExists('config_user_vips');
    }
}
