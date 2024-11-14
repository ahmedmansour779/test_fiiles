<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOfferLangTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('offer_langs', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();

            $table->string('detail_title')->nullable();
            $table->string('listing_title')->nullable();

            $table->text('lang');

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
        Schema::dropIfExists('offer_langs');
    }
}
