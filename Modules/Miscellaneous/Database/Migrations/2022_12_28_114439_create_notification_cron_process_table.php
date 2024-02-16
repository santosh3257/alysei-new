<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNotificationCronProcessTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notification_cron_process', function (Blueprint $table) {
            $table->bigIncrements('notification_cron_process_id');
            $table->bigInteger('cron_job_id')->unsigned();
            $table->bigInteger('user_id');
            $table->enum('status',[0,1,2])->default(0)->comment("0 = not started, 1=success, 2= failed");
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
        Schema::dropIfExists('notification_cron_process');
    }
}
