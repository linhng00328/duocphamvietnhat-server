<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserAdviceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_advice', function (Blueprint $table) {
            $table->id();
            $table->string('username')->nullable();
            $table->string('area_code')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('email')->nullable();
            $table->string('name')->nullable();
            $table->integer("sex")->default(0)->nullable();
            $table->longText('address')->nullable();
            $table->timestamp('date_of_birth')->nullable();
            $table->string('avatar_image')->nullable();
            $table->longText('note')->nullable();
            $table->integer("status")->default(0)->nullable();
            $table->integer("id_employee_help")->nullable();

            $table->longText('consultation_1')->nullable();
            $table->longText('consultation_2')->nullable();
            $table->longText('consultation_3')->nullable();

            $table->timestamp('time_update_consultation_1')->nullable();
            $table->timestamp('time_update_consultation_2')->nullable();
            $table->timestamp('time_update_consultation_3')->nullable();
            
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
        Schema::dropIfExists('user_advice');
    }
}
