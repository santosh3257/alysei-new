<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMarketplaceProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('marketplace_products', function (Blueprint $table) {
            $table->increments('marketplace_product_id');
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('user_id')->on('users');

            $table->unsignedBigInteger('marketplace_store_id');
            $table->string('title');
            $table->string('slug');
            $table->longText('description');
            $table->string('keywords')->nullable();
            $table->integer('product_category_id');
            $table->integer('product_subcategory_id')->nullable();
            $table->string('quantity_available');
            $table->string('brand_label_id')->nullable();
            $table->string('min_order_quantity');
            $table->longtext('handling_instruction');
            $table->longtext('dispatch_instruction');
            $table->string('available_for_sample');
            $table->decimal('product_price', 10, 2);
            $table->enum('status',['0','1'])->default('1')->comment('0=inactive, 1=active');
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
        Schema::dropIfExists('marketplace_products');
    }
}
