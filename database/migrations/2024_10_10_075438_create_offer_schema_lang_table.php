<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOfferSchemaLangTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('offer_schema_langs', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->string('title')->nullable();

            $table->text('lang');

            $table->integer('offer_schema_id')->unsigned();

            $table->foreign('offer_schema_id')
                ->references('id')
                ->on('offer_schemas');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('offer_schema_langs');
    }
}
