<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('username')->nullable();
            $table->string('area_code')->nullable();
            $table->string('phone_number')->nullable();
            $table->timestamp('phone_verified_at')->nullable();
            $table->string('email')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->string('name')->nullable();
            $table->integer("sex")->default(0)->nullable();
            $table->longText('address')->nullable();
            $table->timestamp('date_of_birth')->nullable();
            $table->string('avatar_image')->nullable();
            $table->double("salary")->default(0)->nullable();
            $table->integer('id_decentralization')->default(0)->nullable(); //0 nv sale binh thuong, 1 quan ly doi sale
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
        Schema::dropIfExists('employees');
    }
}
