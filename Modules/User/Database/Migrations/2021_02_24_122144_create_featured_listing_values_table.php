<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFeaturedListingValuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('featured_listing_values', function (Blueprint $table) {
            $table->increments('featured_listing_value_id');
            $table->bigInteger('user_id');
            $table->integer('featured_listing_field_id');
            $table->bigInteger('featured_listing_id');
            $table->string('value');
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
        Schema::dropIfExists('featured_listing_values');
    }
}
