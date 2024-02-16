<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMarketplaceStoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('marketplace_stores', function (Blueprint $table) {
            $table->increments('marketplace_store_id');
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('user_id')->on('users');
            $table->integer('package_id');
            $table->string('name');
            $table->string('slug');
            $table->longText('description');
            $table->string('website');
            $table->string('phone');
            $table->integer('store_region');
            $table->string('location');
            $table->string('lattitude');
            $table->string('longitude');
            $table->string('logo_id');
            $table->string('banner_id');
            $table->enum('status',['0','1','2'])->default('0')->comment('0=pending for approval, 1=active, 2=disabled');
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
        Schema::dropIfExists('marketplace_stores');
    }
}
