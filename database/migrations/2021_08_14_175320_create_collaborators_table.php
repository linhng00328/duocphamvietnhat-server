<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCollaboratorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('collaborators', function (Blueprint $table) {
            $table->id();


            $table->unsignedBigInteger('store_id')->unsigned()->index();
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');

            $table->unsignedBigInteger('customer_id')->unsigned()->index();
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');

            $table->unique(['customer_id']);

            $table->boolean("payment_auto")->default(false)->nullable();
            $table->double("balance")->default(0)->nullable(); 
            
            $table->string("first_and_last_name")->nullable(); 
            $table->string("cmnd")->nullable(); 
            $table->timestamp('date_range')->nullable();
            $table->string("issued_by")->nullable(); 
            $table->string("front_card")->nullable(); 
            $table->string("back_card")->nullable(); 

            $table->integer("status")->nullable(); 
            

            $table->string("bank")->nullable(); 
            $table->string("account_number")->nullable(); 
            $table->string("account_name")->nullable(); 
            $table->string("branch")->nullable(); 
  
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
        Schema::dropIfExists('collaborators');
    }
}
