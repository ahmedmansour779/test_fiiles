<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOfferCategoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('offer_categories', function (Blueprint $table) {
            $table->id();
            $table->timestamps();


            $table->bigInteger('category_id')->unsigned();

            $table->foreign('category_id')
                ->references('id')
                ->on('categories');

            $table->integer('offer_id')->unsigned();

            $table->foreign('offer_id')
                ->references('id')
                ->on('offers');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('offer_categories');
    }
}
