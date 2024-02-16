<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMarketplaceMapCalssTaxesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('marketplace_map_calss_taxes', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('class_id')->unsigned()->index()->nullable();
            $table->bigInteger('tax_id')->unsigned()->index()->nullable();
            $table->softDeletes();
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
        Schema::dropIfExists('marketplace_map_calss_taxes');
    }
}
