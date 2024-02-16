<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserProfileProgressTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_profile_progress', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('role_id');
            $table->text('profile_img')->nullable();
            $table->text('cover_img')->nullable();
            $table->text('hub')->nullable();
            $table->text('about')->nullable();
            $table->text('contact')->nullable();
            $table->text('products')->nullable();
            $table->text('featured')->nullable();
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
        Schema::dropIfExists('user_profile_progress');
    }
}
