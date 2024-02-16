<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFeaturedListingFieldsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('featured_listing_fields', function (Blueprint $table) {
            $table->increments('featured_listing_field_id');
            $table->string('title');
            $table->string('placeholder')->nullable();
            $table->string('name')->nullable();
            $table->string('type');
            $table->string('description')->nullable();
            $table->string('hint')->nullable();
            $table->enum('required',['yes','no'])->default('no');
            $table->text('css')->nullable();
            $table->string('class_name')->nullable();
            $table->enum('conditional',['yes','no'])->default('no');
            $table->enum('api_call',['true','false'])->default('false');
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
        Schema::dropIfExists('featured_listing_fields');
    }
}
