<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMarketplaceOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('marketplace_orders', function (Blueprint $table) {
            $table->increments('order_id');
            $table->bigInteger('seller_id')->unsigned()->index()->nullable();
            $table->foreign('seller_id')->references('user_id')->on('users');
            $table->bigInteger('buyer_id')->unsigned()->index()->nullable();
            $table->foreign('buyer_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->bigInteger('store_id')->unsigned()->index()->nullable();
            $table->bigInteger('num_items_sold')->unsigned()->nullable();
            $table->bigInteger('total_seles')->unsigned()->nullable();
            $table->decimal('tax_total',10,2)->nullable(false)->default(0.00);
            $table->decimal('returning_customer',10,2)->nullable(false)->default(0.00);
            $table->decimal('shipping_total',10,2)->nullable(false)->default(0.00);
            $table->decimal('net_total',10,2)->nullable(false)->default(0.00);
            $table->string('currency')->default('$');
            $table->bigInteger('billing_id')->nullable();
            $table->bigInteger('shipping_id')->nullable();
            $table->enum('status', ['pending', 'processing','on hold','completed','cancelled','failed'])->default('pending');
            $table->string('invoice_name',200)->nullable();
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
        Schema::dropIfExists('marketplace_orders');
    }
}
