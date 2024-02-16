<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMarketplacePackagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('marketplace_packages', function (Blueprint $table) {
            $table->increments('marketplace_package_id');
            $table->string('name');
            $table->integer('duration')->nullable();
            $table->decimal('amount', 10, 2)->default('0.00');
            $table->enum('status',['1','0'])->default('1');
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
        Schema::dropIfExists('marketplace_packages');
    }
}
