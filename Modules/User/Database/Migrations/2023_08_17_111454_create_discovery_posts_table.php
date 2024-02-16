<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDiscoveryPostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('discovery_posts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title',191)->nullable(false);
            $table->string('title_it',191)->nullable(false);
            $table->string('slug',191)->nullable(false);
            $table->string('email',191)->nullable(false);
            $table->integer('country_code')->nullable(false);
            $table->string('phone_number',20)->nullable(false);
            $table->integer('category_id')->nullable();
            $table->longText('description')->nullable();
            $table->string('url')->nullable();
            $table->enum('status',[0,1])->default(0)->comment("0=Approve,1=Not Approve");
            $table->integer('image_id')->nullable();
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
        Schema::dropIfExists('discovery_posts');
    }
}
