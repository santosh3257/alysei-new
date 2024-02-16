<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRecipesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('recipes', function (Blueprint $table) {
            $table->increments('recipe_id');
            $table->integer('user_id');
            $table->string('name')->nullable();
            $table->string('slug');
            $table->integer('meal_id')->nullable();
            $table->integer('course_id')->nullable();
            $table->integer('hours')->comment('preparation time')->nullable();
            $table->integer('minutes')->comment('preparation time')->nullable();
            $table->integer('serving')->nullable();
            $table->integer('cousin_id')->nullable();
            $table->integer('region_id')->nullable();
            $table->integer('diet_id')->nullable();
            $table->integer('intolerance_id')->nullable();
            $table->integer('cooking_skill_id')->nullable();
            $table->integer('image_id')->nullable();
            $table->integer('favourite_count')->default(0);
            $table->integer('no_of_ingredients')->default(0);
            $table->enum('status',['0','1'])->default('0')->comment("1=published','0=draft");
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
        Schema::dropIfExists('recipes');
    }
}
