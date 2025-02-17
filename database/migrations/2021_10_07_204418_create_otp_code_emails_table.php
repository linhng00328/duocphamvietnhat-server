<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOtpCodeEmailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('otp_code_emails', function (Blueprint $table) {


            $table->id();
            $table->string("otp")->nullable(); 
            $table->string("email")->nullable(); 
            $table->timestamp("time_generate")->nullable(); 
            $table->longText("content")->nullable(); 

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
        Schema::dropIfExists('otp_code_emails');
    }
}
