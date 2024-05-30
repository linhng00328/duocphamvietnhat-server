<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTallySheetItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tally_sheet_items', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('store_id')->unsigned()->index();
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');
            
            $table->unsignedBigInteger('branch_id')->unsigned()->index();
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');

            $table->unsignedBigInteger('tally_sheet_id')->unsigned()->index();
            $table->foreign('tally_sheet_id')->references('id')->on('tally_sheets')->onDelete('cascade');

            $table->unsignedBigInteger('product_id')->unsigned()->index();
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');

            $table->unsignedBigInteger('element_distribute_id')->unsigned()->nullable()->index();
            $table->foreign('element_distribute_id')->references('id')->on('element_distributes')->onDelete('cascade');
            
            $table->unsignedBigInteger('sub_element_distribute_id')->unsigned()->nullable()->index();
            $table->foreign('sub_element_distribute_id')->references('id')->on('sub_element_distributes')->onDelete('cascade');
           

            $table->integer("existing_branch")->default(0)->nullable();
            $table->integer("reality_exist")->default(0)->nullable();
            $table->integer("deviant")->default(0)->nullable();
            
            $table->string("distribute_name")->nullable();
            $table->string("element_distribute_name")->nullable();
            $table->string("sub_element_distribute_name")->nullable();

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
        Schema::dropIfExists('tally_sheet_items');
    }
}
