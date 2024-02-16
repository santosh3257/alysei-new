<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRecipeMapStepsToolsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('recipe_map_steps_tools', function (Blueprint $table) {
            $table->increments('recipe_map_steps_tool_id');
            $table->integer('recipe_id')->nullable();
            $table->integer('recipe_step_id')->nullable();
            $table->integer('recipe_saved_tool_id')->nullable();
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
        Schema::dropIfExists('recipe_map_steps_tools');
    }
}
