<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMarketplaceProductEnqueriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('marketplace_product_enqueries', function (Blueprint $table) {
            $table->increments('marketplace_product_enquery_id');
            $table->integer('user_id')->unsigned();
            $table->integer('producer_id')->unsigned();
            $table->integer('product_id');
            $table->string('name');
            $table->string('email');
            $table->string('phone');
            $table->enum('sender_status',['1','2'])->default('1')->comment('1=open, 2=closed');
            $table->enum('receiver_status',['0','1','2'])->default('0')->comment('0=new, 1=open, 2=closed');
            $table->text('message');
            $table->enum('status',['0','1','2'])->default('1')->comment('0=new, 1=open, 2=closed');
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
        Schema::dropIfExists('recipe_enqueries');
    }
}
