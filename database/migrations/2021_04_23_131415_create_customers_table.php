<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('store_id')->unsigned()->index();
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');
            $table->string('username')->nullable();
            $table->boolean("official")->default(1)->nullable();
            $table->string('area_code')->nullable();
            $table->string('phone_number');

            $table->unique(['phone_number', 'store_id']);

            $table->double("points_awarded_to_customer")->default(0)->nullable();

            $table->timestamp('phone_verified_at')->nullable();
            $table->string('email')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->double("debt")->default(0)->nullable();
            $table->string('referral_phone_number')->nullable();
            $table->string('name')->nullable();
            $table->string('name_str_filter')->nullable();
            $table->timestamp('date_of_birth')->nullable();
            $table->string('avatar_image')->nullable();
            $table->integer("points")->default(0)->nullable();
            $table->integer("sex")->default(0)->nullable();
            $table->boolean("is_collaborator")->default(0)->nullable();
            $table->boolean("is_agency")->default(0)->nullable();
            $table->boolean("is_sale")->default(0)->nullable();
            $table->boolean("is_passersby")->default(0)->nullable();
            $table->boolean("is_from_json")->default(0)->nullable();

            $table->string("address_detail")->nullable();
            $table->integer("country")->nullable();
            $table->integer("province")->nullable();
            $table->integer("district")->nullable();
            $table->integer("wards")->nullable();

            $table->string("country_name")->nullable();
            $table->string("province_name")->nullable();
            $table->string("district_name")->nullable();
            $table->string("wards_name")->nullable();



            $table->integer("notifications_count")->default(0)->nullable();
            $table->integer("sale_staff_id")->nullable();
            $table->timestamp("time_sale_staff")->nullable();

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
        Schema::dropIfExists('customers');
    }
}
