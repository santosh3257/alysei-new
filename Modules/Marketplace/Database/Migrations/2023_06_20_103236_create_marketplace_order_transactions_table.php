<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMarketplaceOrderTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('marketplace_order_transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('seller_id')->unsigned()->index()->nullable();
            $table->foreign('seller_id')->references('user_id')->on('users');
            $table->bigInteger('buyer_id')->unsigned()->index()->nullable();
            $table->foreign('buyer_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->bigInteger('order_id')->unsigned()->index()->nullable();
            $table->text('transaction_id')->nullable();
            $table->text('charge_id')->nullable();
            $table->text('intent_id')->nullable();
            $table->float('paid_amount',10,2)->nullable(false)->default(0.0);
            $table->enum('status',['success','pending','failed'])->nullable(false)->default('pending');
            $table->string('currency')->default('$');
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
        Schema::dropIfExists('marketplace_order_transactions');
    }
}
