<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSaleVisitAgenciesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sale_visit_agencies', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('store_id')->index();
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');
            $table->unsignedBigInteger('staff_id')->index();
            $table->foreign('staff_id')->references('id')->on('staff')->onDelete('cascade');
            $table->unsignedBigInteger('agency_id')->index();
            $table->foreign('agency_id')->references('id')->on('agencies')->onDelete('cascade');
            $table->boolean('is_agency_open')->default(0);
            $table->timestamp('time_checkin')->nullable();
            $table->timestamp('time_checkout')->nullable();
            $table->integer('time_visit')->default(0);
            $table->float('latitude')->nullable();
            $table->float('longitude')->nullable();
            $table->float('long_checkout')->nullable();
            $table->float('lat_checkout')->nullable();
            $table->longText('note')->nullable();
            $table->longText('images')->nullable();
            $table->string('address_checkin')->nullable();
            $table->string('device_pin')->nullable();

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
        Schema::dropIfExists('sale_visit_agencies');
    }
}
