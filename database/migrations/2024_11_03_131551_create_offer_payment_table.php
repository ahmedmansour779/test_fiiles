<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOfferPaymentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('offer_payments', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();


            $table->integer('payment_method_id')->unsigned();

            $table->foreign('payment_method_id')
                ->references('id')
                ->on('payment_methods');

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
        Schema::dropIfExists('offer_payments');
    }
}
