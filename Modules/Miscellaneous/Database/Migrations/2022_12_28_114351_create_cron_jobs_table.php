<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCronJobsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cron_jobs', function (Blueprint $table) {
            $table->bigIncrements('cron_job_id');
            $table->string('cron_job_title');
            $table->string('cron_job_title_it');
            $table->enum('cron_status',[0,1,2,3])->default(0)->comment("0 = not started, 1=in process, 2= completed,3=failed");
            $table->string('remarks')->nullable();
            $table->enum('cron_type',['notification'])->default('notification');
            $table->text('message_en');
            $table->text('message_it');
            $table->text('error_trace')->nullable();
            $table->integer('attachment_id');
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
        Schema::dropIfExists('cron_jobs');
    }
}
