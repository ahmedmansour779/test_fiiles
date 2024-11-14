<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOfferBrandTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('offer_brands', function (Blueprint $table) {
            $table->id();
            $table->timestamps();


            $table->bigInteger('brand_id')->unsigned();

            $table->foreign('brand_id')
                ->references('id')
                ->on('brands');


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
        Schema::dropIfExists('offer_brands');
    }
}
