<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMarketplaceOrderItemTaxesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('marketplace_order_item_taxes', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('order_id')->unsigned()->index()->nullable();
            $table->bigInteger('order_item_id')->unsigned()->index()->nullable();
            $table->string('tax_name',250)->nullable(false);
            $table->float('tax_rate')->nullable();
            $table->string('tax_type')->nullable();
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
        Schema::dropIfExists('marketplace_order_item_taxes');
    }
}
