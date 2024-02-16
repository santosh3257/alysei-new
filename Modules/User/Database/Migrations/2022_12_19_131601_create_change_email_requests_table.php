<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChangeEmailRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('change_email_requests', function (Blueprint $table) {
            $table->increments('change_email_request_id');
            $table->bigInteger('user_id')->unique();
            $table->string('email');
            $table->enum('device_type',['web','android','ios'])->unique();
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
        Schema::dropIfExists('change_email_requests');
    }
}
