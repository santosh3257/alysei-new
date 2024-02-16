<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('events', function (Blueprint $table) {
            $table->increments('event_id');
            $table->integer('user_id');
            $table->string('event_name');
            $table->string('slug');
            $table->string('host_name');
            $table->string('location');
            $table->string('date');
            $table->string('time');
            $table->text('description');
            $table->string('website');
            $table->enum('event_type',['public','private']);
            $table->enum('registration_type',['free','paid']);
            $table->string('url')->nullable();
            $table->integer('image_id');
            $table->integer('like_counts')->default(0);
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
        Schema::dropIfExists('events');
    }
}
