<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWalkThroughScreensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('walk_through_screens', function (Blueprint $table) {
            $table->increments('walk_through_screen_id');
            $table->integer('role_id')->unsigned();
            $table->string('step');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('image_id')->nullable();
            $table->integer('order')->unsigned();
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
        Schema::dropIfExists('walk_through_screens');
    }
}
