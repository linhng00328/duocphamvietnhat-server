<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChangeBalanceCollaboratorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('change_balance_collaborators', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('store_id')->unsigned()->index();
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');

            $table->unsignedBigInteger('collaborator_id')->unsigned()->index();
            $table->foreign('collaborator_id')->references('id')->on('collaborators')->onDelete('cascade');

            $table->integer("type")->nullable();
            $table->double("current_balance")->default(0)->nullable();
            $table->double("money")->default(0)->nullable();
            $table->integer("references_id")->nullable();
            $table->string("references_value")->nullable();
            $table->string("note")->nullable();

            $table->unique(
                [
                    'store_id',
                    'collaborator_id',
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
        Schema::dropIfExists('change_balance_collaborators');
    }
}
