<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateActivityActionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('activity_actions', function (Blueprint $table) {
            $table->increments('activity_action_id');
            $table->integer('type')->default(0);
            $table->string('subject_type');
            $table->string('slug');
            $table->integer('subject_id');
            $table->string('object_type');
            $table->integer('object_id');
            $table->text('body')->nullable();
            $table->text('params')->nullable();
            $table->integer('shared_post_id')->default(0);
            $table->integer('attachment_count')->default(0);
            $table->integer('comment_count')->default(0);
            $table->integer('like_count')->default(0);
            $table->string('privacy')->nullable();
            $table->string('height')->nullable();
            $table->string('width')->nullable();
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
        Schema::dropIfExists('activity_actions');
    }
}
