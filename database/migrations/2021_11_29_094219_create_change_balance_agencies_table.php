<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChangeBalanceAgenciesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('change_balance_agencies', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('store_id')->unsigned()->index();
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');

            $table->unsignedBigInteger('agency_id')->unsigned()->index();
            $table->foreign('agency_id')->references('id')->on('agencies')->onDelete('cascade');

            $table->integer("type")->nullable();
            $table->double("current_balance")->default(0)->nullable();
            $table->double("money")->default(0)->nullable();
            $table->integer("references_id")->nullable();
            $table->string("references_value")->nullable();
            $table->string("note")->nullable();

            $table->unique(
                [
                    'store_id',
                    'agency_id',
                    'type',
                    'references_id',
                    'references_value'
                ],
                'one1'
            );


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
        Schema::dropIfExists('change_balance_agencies');
    }
}
