<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMarketPlaceOrderItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('marketplace_order_items', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('order_id')->unsigned()->index()->nullable();
            $table->bigInteger('product_id')->unsigned()->index()->nullable();
            $table->bigInteger('tax_class_id')->unsigned()->index()->nullable();
            $table->bigInteger('offer_map_id')->unsigned()->index()->nullable();
            $table->decimal('product_price',10,2)->nullable(false)->default(0.00);
            $table->bigInteger('quantity')->nullable();
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
        Schema::dropIfExists('marketplace_order_items');
    }
}
