<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMarketplaceProductGalleriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('marketplace_product_galleries', function (Blueprint $table) {
            $table->increments('marketplace_product_gallery_id');
            $table->integer('marketplace_product_id');
            $table->string('attachment_url');
            $table->string('base_url');
            $table->string('attachment_type');
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
        Schema::dropIfExists('marketplace_product_galleries');
    }
}
