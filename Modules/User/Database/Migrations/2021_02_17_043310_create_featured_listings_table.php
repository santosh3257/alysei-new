<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFeaturedListingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('featured_listings', function (Blueprint $table) {
            $table->bigIncrements('featured_listing_id');
            $table->bigInteger('user_id');
            $table->integer('featured_listing_type_id');
            $table->string('title');
            $table->string('slug');
            $table->longText('description')->nullable();
            $table->string('image_id')->nullable();
            $table->text('anonymous')->nullable();
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
        Schema::dropIfExists('featured_listings');
    }
}
