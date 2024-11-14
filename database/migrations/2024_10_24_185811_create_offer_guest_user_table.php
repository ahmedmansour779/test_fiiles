<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOfferGuestUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('offer_guest_users', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->bigInteger('guest_user_id')->unsigned();

            $table->foreign('guest_user_id')
                ->references('id')
                ->on('guest_users');


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
        Schema::dropIfExists('offer_guest_users');
    }
}
