<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFeaturedListingOptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('featured_listing_field_options', function (Blueprint $table) {
            $table->increments('featured_listing_option_id');
            $table->integer('featured_listing_field_id');
            $table->string('option');
            $table->integer('parent')->default(0)->comment("parent store self table id i.e:user_field_option_id");
            $table->integer('head')->default(0);
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
        Schema::dropIfExists('featured_listing_field_options');
    }
}
