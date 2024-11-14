<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOfferMetaLangTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('offer_meta_langs', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();


            $table->text('meta_description')->nullable();
            $table->text('meta_keywords')->nullable();
            $table->text('meta_title')->nullable();


            $table->text('lang');

            $table->integer('offer_meta_id')->unsigned();

            $table->foreign('offer_meta_id')
                ->references('id')
                ->on('offer_metas');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('offer_meta_langs');
    }
}
