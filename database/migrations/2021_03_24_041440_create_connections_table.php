<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateConnectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('connections', function (Blueprint $table) {
            $table->increments('connection_id');
            $table->integer('resource_id');
            $table->integer('user_id');
            $table->text('reason_to_connect')->nullabel();
            $table->enum('is_approved',['0','1'])->default(0)->comment("0=Pending,1=Approved");
            $table->text('product_ids')->nullabel();
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
        Schema::dropIfExists('connections');
    }
}
