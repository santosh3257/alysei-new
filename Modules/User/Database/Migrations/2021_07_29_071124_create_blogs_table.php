<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBlogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('blogs', function (Blueprint $table) {
            $table->increments('blog_id');
            $table->integer('user_id');
            $table->string('title');
            $table->string('slug');
            $table->string('date');
            $table->string('time');
            $table->text('description');
            $table->integer('image_id');
            $table->enum('status',['1','0'])->comment('1= active, 0=draft');
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
        Schema::dropIfExists('blogs');
    }
}
