<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMarketplaceWalkthroughScreensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('marketplace_walkthrough_screens', function (Blueprint $table) {
            $table->increments('marketplace_walkthrough_screen_id');
            $table->integer('role_id');
            $table->string('step');
            $table->string('title_en');
            $table->string('title_it');
            $table->text('description_en')->nullable();
            $table->text('description_it')->nullable();
            $table->string('image_id')->nullable();
            $table->integer('order')->unsigned();
            $table->enum('status',['1','0'])->default('1');
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
        Schema::dropIfExists('marketplace_walkthrough_screens');
    }
}
