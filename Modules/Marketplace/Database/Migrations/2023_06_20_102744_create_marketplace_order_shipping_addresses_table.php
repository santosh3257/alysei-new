<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMarketplaceOrderShippingAddressesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('marketplace_order_shipping_addresses', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('user_id')->unsigned()->index()->nullable();
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->string('first_name',250)->nullable(true);
            $table->string('last_name',250)->nullable(true);
            $table->string('email',250)->nullable(true);
            $table->string('company_name',250)->nullable(true);
            $table->string('street_address',250)->nullable(true);
            $table->string('street_address_2',250)->nullable(true);
            $table->string('city',250)->nullable(true);
            $table->string('state',250)->nullable(true);
            $table->string('country',250)->nullable(true);
            $table->string('zipcode',250)->nullable(true);
            $table->softDeletes();
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
        Schema::dropIfExists('marketplace_order_shipping_addresses');
    }
}
