<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTripsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trips', function (Blueprint $table) {
            $table->increments('trip_id');
            $table->integer('user_id');
            $table->string('trip_name');
            $table->string('slug');
            $table->string('travel_agency');
            $table->integer('country');
            $table->integer('region');
            $table->integer('adventure_type');
            $table->string('duration');
            $table->string('intensity');
            $table->string('website');
            $table->string('price');
            $table->text('description');
            $table->string('image_id');
            $table->enum('status',['1','0'])->default('1')->comment('1= active, 0=inactive');
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
        Schema::dropIfExists('trips');
    }
}
