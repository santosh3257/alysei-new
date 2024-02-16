<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMarketplaceReviewRatingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('marketplace_review_ratings', function (Blueprint $table) {
            $table->increments('marketplace_review_rating_id');
            $table->integer('user_id');
            $table->integer('id');
            $table->integer('rating');
            $table->text('review')->nullable();
            $table->enum('type',['1','2'])->comment('1=store, 2=product');
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
        Schema::dropIfExists('marketplace_review_ratings');
    }
}
