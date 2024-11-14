<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Config;

class CreateOfferTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('offers', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();

            $table->string('detail_title')->nullable();
            $table->string('listing_title')->nullable();
            $table->boolean('allow_coupon')->nullable();

            $table->timestamp('start_time')->nullable();
            $table->timestamp('end_time')->nullable();
            $table->integer('purchase_count_limit')->nullable();
            $table->decimal('total_value_limit')->nullable();
            $table->boolean('status')->default(false);


            $table->integer('vendor_id')->unsigned()->nullable();

            $table->foreign('vendor_id')
                ->references('id')
                ->on('admins');

            $table->integer('admin_id')->unsigned();

            $table->foreign('admin_id')
                ->references('id')
                ->on('admins');

            $table->integer('shipping_rule_id')->unsigned()->nullable();

            $table->foreign('shipping_rule_id')
                ->references('id')
                ->on('shipping_rules');

            $table->integer('payment_type')->nullable();

            $table->integer('offer_schema_id')->unsigned()->nullable();

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
        Schema::dropIfExists('offers');
    }
}
