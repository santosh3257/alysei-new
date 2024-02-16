<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCousinsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cousins', function (Blueprint $table) {
            $table->increments('cousin_id');
            $table->string('name');
            $table->integer('image_id');
            $table->enum('status',['1','0'])->default('1= active, 0=inactive')->default('1');
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
        Schema::dropIfExists('cousins');
    }
}
