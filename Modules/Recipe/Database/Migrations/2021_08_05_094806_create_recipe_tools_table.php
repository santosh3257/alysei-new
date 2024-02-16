<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRecipeToolsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('recipe_tools', function (Blueprint $table) {
            $table->increments('recipe_tool_id');
            $table->string('title');
            $table->string('name');
            $table->integer('image_id');
            $table->integer('parent')->default(0)->comment('parent store self table id i.e., recipe_tool_id');
            $table->tinyInteger('featured')->default(0);
            $table->integer('priority')->default(0);
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
        Schema::dropIfExists('recipe_tools');
    }
}
