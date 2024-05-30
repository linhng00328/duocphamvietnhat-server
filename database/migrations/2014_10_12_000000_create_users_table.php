<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('area_code')->nullable();
            $table->string('phone_number')->unique()->nullable();
            $table->timestamp('phone_verified_at')->nullable();
            $table->string('email')->unique()->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('name')->nullable();
            $table->timestamp('date_of_birth')->nullable();
            $table->string('avatar_image')->nullable();
            $table->longText('functions_json')->nullable();

            $table->integer("score")->default(0)->nullable();
            $table->integer("sex")->default(0)->nullable();
            $table->integer('create_maximum_store')->default(1)->nullable();
            $table->boolean("is_vip")->default(false)->nullable();
            $table->boolean("is_block")->default(false)->nullable();

            

            $table->timestamp('last_visit_time')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        User::create(
            [
                'area_code' => "+84",
                'phone_number' => "0123456789",
                'email' => "test@gmail.com",
                'password' => bcrypt("123")
            ]
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
