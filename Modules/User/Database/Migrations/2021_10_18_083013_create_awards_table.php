<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAwardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('awards', function (Blueprint $table) {
            $table->increments('award_id');
            $table->integer('user_id');
            $table->string('award_name');
            $table->string('winning_product');
            $table->string('medal_id');
            $table->string('competition_url')->nullable();
            $table->integer('image_id');
            $table->enum('status',['1','0'])->comment('1= active, 0=inactive');
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
        Schema::dropIfExists('awards');
    }
}
