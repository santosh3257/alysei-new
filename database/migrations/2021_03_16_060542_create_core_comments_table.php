<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCoreCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('core_comments', function (Blueprint $table) {
            $table->increments('core_comment_id');
            $table->string('resource_type');
            $table->integer('resource_id');
            $table->string('poster_type');
            $table->integer('poster_id');
            $table->text('body');
            $table->integer('parent_id')->default(0);
            $table->text('params')->nullable(); 
            $table->integer('like_count')->default(0);
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
        Schema::dropIfExists('core_comments');
    }
}
