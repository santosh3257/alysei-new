<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserFieldsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_fields', function (Blueprint $table) {
            $table->increments('user_field_id');
            $table->string('title');
            $table->string('placeholder')->nullable();
            $table->string('name')->nullable();
            $table->string('type');
            $table->string('description')->nullable();
            $table->string('hint')->nullable();
            $table->enum('required',['yes','no'])->default('no');
            $table->text('css')->nullable();
            $table->string('class_name')->nullable();
            $table->enum('conditional',['yes','no'])->default('no');
            $table->enum('api_call',['true','false'])->default('false');
            $table->enum('require_update',['true','false'])->default('false');
            $table->enum('display_on_registration',['true','false'])->default('true');
            $table->enum('display_on_dashboard',['true','false'])->default('true');
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
        Schema::dropIfExists('user_fields');
    }
}
