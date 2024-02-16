<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateConnectFollowPermissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('connect_follow_permissions', function (Blueprint $table) {
            $table->increments('connect_follow_permission_id');
            $table->integer('role_id');
            $table->enum('permission_type',['1','2'])->default(1)->comment("1=Connect Permission,2=Follow Permission");
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
        Schema::dropIfExists('connect_follow_permissions');
    }
}
