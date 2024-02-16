<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDiscoverAlyseiTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('discover_alysei', function (Blueprint $table) {
            $table->increments('discover_alysei_id');
            $table->string('title');
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('image_id');
            $table->enum('status',[0,1])->default(1)->comment("0=Inactive,1=Active");
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
        Schema::dropIfExists('discover_alysei');
    }
}
