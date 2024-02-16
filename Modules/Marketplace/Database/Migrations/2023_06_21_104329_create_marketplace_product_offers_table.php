<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMarketplaceProductOffersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('marketplace_product_offers', function (Blueprint $table) {
            $table->increments('offer_id');
            $table->string('offer_name',250);
            $table->bigInteger('seller_id')->unsigned()->index()->nullable();
            $table->bigInteger('buyer_id')->unsigned()->index()->nullable();
            $table->date('end_date')->nullable(false);
            $table->enum('payment_term',['instant','10 days','20 days','30 days','other'])->nullable(false)->default('instant');
            $table->text('other_term')->nullable();
            $table->float('shipping_price',10,2)->nullable(false)->default(0.0);
            $table->enum('include_shipping_charge',['true','false'])->nullable(false)->default('true');
            $table->enum('status',['pending','accepted','rejected','paid'])->nullable(false)->default('pending');
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
        Schema::dropIfExists('marketplace_product_offers');
    }
}
