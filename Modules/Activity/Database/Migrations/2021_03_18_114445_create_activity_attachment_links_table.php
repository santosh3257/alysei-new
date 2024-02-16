<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateActivityAttachmentLinksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('activity_attachment_links', function (Blueprint $table) {
            $table->increments('activity_attachment_link_id');
            $table->string('attachment_url');
            $table->string('attachment_type');
            $table->string('base_url')->default("https://alyseiapi.ibyteworkshop.com/");
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
        Schema::dropIfExists('activity_attachment_links');
    }
}
