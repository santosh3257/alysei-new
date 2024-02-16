<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMarketplaceFavouritesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('marketplace_favourites', function (Blueprint $table) {
            $table->increments('marketplace_favourite_id');
            $table->integer('user_id');
            $table->integer('id');
            $table->enum('favourite_type',['1','2'])->comment('1=store, 2=product');
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
        Schema::dropIfExists('marketplace_favourites');
    }
}
