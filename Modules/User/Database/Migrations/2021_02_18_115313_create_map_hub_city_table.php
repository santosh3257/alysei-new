<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMapHubCityTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('map_hub_city', function (Blueprint $table) {
            $table->increments('map_hub_city_id');
            $table->integer('hub_id');
            $table->integer('city_id');
            $table->enum('status',[0,1])->default(1)->comment("1=Active,0=Inactive");
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
        Schema::dropIfExists('map_hub_city');
    }
}
