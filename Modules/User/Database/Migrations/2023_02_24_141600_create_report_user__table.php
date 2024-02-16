<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReportUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('report_users', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->integer('report_by');
            $table->string('report_as')->nullable();
            $table->text('message')->nullable();
            $table->text('remarks')->nullable();
            $table->enum('status',[0,1])->default(0)->comment("0=Approve,1=Not Approve");
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
        Schema::dropIfExists('report_users');
    }
}
