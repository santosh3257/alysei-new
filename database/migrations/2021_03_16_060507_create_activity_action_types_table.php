<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateActivityActionTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('activity_action_types', function (Blueprint $table) {
            $table->increments('activity_action_type_id');
            $table->string('type');
            $table->text('body');
            $table->enum('enabled',['0','1'])->default('1')->comment("0=Not Enabled, 1=Enabled");
            $table->enum('displayable',['0','1'])->default('1')->comment("0=Not Displayble, 1=Displayble");
            $table->enum('attachable',['0','1'])->default('1')->comment("0=Not Attachable, 1=Attachable");
            $table->enum('commentable',['0','1'])->default('1')->comment("0=Not Commentable, 1=Commentable");
            $table->enum('shareable',['0','1'])->default('1')->comment("0=Not Shareable, 1=Shareable");
            $table->enum('editable',['0','1'])->default('1')->comment("0=Not Editable, 1=Editable");
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
        Schema::dropIfExists('activity_action_types');
    }
}
