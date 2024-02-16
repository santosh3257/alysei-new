<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePaymentSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_settings', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('user_id')->unsigned()->index()->nullable();
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->string('account_holder_name',200)->nullable();
            $table->string('bank_name',200)->nullable();
            $table->string('account_number',200)->nullable();
            $table->string('swift_code',100)->nullable();
            $table->text('bank_address')->nullable();
            $table->string('paypal_id',100)->nullable();
            $table->integer('payment_limit')->nullable();
            $table->string('country',100)->nullable();
            $table->string('city',100)->nullable();
            $table->enum('payment_option',['paypal', 'bank'])->default('bank');
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
        Schema::dropIfExists('payment_settings');
    }
}
