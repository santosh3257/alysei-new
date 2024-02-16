<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMarketplaceStoreGalleriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('marketplace_store_galleries', function (Blueprint $table) {
            $table->increments('marketplace_store_gallery_id');
            $table->integer('marketplace_store_id');
            $table->string('attachment_url');
            $table->string('attachment_type');
            $table->string('base_url');
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
        Schema::dropIfExists('marketplace_store_galleries');
    }
}
