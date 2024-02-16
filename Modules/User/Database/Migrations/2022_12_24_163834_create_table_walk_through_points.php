<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableWalkThroughPoints extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('walk_through_points', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title_en');
            $table->string('title_it')->nullable();
            $table->text('description_en')->nullable();
            $table->text('description_it')->nullable();
            $table->bigInteger('icon_id')->nullable();
            $table->integer('walk_through_screen_id');
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
        Schema::dropIfExists('');
    }
}
