<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserFieldOptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_field_options', function (Blueprint $table) {
            $table->increments('user_field_option_id');
            $table->integer('user_field_id');
            $table->string('option');
            $table->integer('parent')->default(0)->comment("parent store self table id i.e:user_field_option_id");
            $table->integer('head')->default(0);
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
        Schema::dropIfExists('user_field_options');
    }
}
