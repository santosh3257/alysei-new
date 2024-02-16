<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMarketplaceBannersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('marketplace_banners', function (Blueprint $table) {
            $table->increments('marketplace_banner_id');
            $table->string('title');
            $table->integer('image_id');
            $table->enum('type',['1','2'])->comment("1=for top, 2=for bottom");
            $table->enum('status',['1','0'])->comment("1=active, 0=inactive");
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
        Schema::dropIfExists('marketplace_banners');
    }
}
