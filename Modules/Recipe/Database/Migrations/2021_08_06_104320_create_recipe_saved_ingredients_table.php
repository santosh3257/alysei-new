<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRecipeSavedIngredientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('recipe_saved_ingredients', function (Blueprint $table) {
            $table->increments('recipe_saved_ingredient_id');
            $table->integer('recipe_id')->nullable();
            $table->integer('ingredient_id')->nullable();
            $table->string('quantity')->nullable();
            $table->string('unit')->nullable();
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
        Schema::dropIfExists('recipe_saved_ingredients');
    }
}
