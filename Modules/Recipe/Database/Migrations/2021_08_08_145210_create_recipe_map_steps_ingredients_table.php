<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRecipeMapStepsIngredientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('recipe_map_steps_ingredients', function (Blueprint $table) {
            $table->increments('recipe_map_steps_ingredient_id');
            $table->integer('recipe_id')->nullable();
            $table->integer('recipe_step_id')->nullable();
            $table->integer('recipe_saved_ingredient_id')->nullable();
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
        Schema::dropIfExists('recipe_map_steps_ingredients');
    }
}
