<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('user_id');
            $table->string('name')->unique()->nullable();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('first_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('country_code')->nullable();
            $table->string('phone')->nullable();
            $table->integer('otp')->nullable();
            $table->string('location')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();
            $table->integer('country_id')->nullable();
            $table->timestamp('registered_date')->nullable();
            $table->timestamp('last_login_date')->nullable();
            $table->string('password_hint')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('user_activation_key')->nullable();
            $table->string('display_name')->nullable();
            $table->enum('account_enabled',['active','inactive','expired','incomplete'])->default('incomplete');
            $table->enum('subscription_type',[0,1])->default(0)->comment("0=Without Subscription,1=Subscription,2=Trial");
            $table->enum('social_user',[0,1])->default(0)->comment("0=No,1=Yes");
            $table->string('social_provider')->nullable();
            $table->string('social_provider_user_id')->nullable();
            $table->string('migrated_id')->nullable();
            $table->string('user_type')->default("Alysei");
            $table->integer('avatar_id')->nullable();
            $table->integer('cover_id')->nullable();
            $table->string('key')->comment("forgot key")->nullable();
            $table->integer('role_id')->unsigned();

            $table->enum('alysei_review',['0','1'])->default('0')->comment("0=Not Reviewed, 1=Reviewed");
            $table->enum('alysei_certification',['0','1'])->default('0')->comment("0=Not Certified, 1=Certified");
            $table->enum('alysei_recognition',['0','1'])->default('0')->comment("0=Not Recognised, 1=Recognised");
            $table->enum('alysei_qualitymark',['0','1'])->default('0')->comment("0=Not Marked, 1=Marked");

            $table->enum('allow_message_from',['anyone','followers','community','nobody'])->default('anyone');
            $table->enum('who_can_view_age',['anyone','followers','community','justme'])->default('anyone');
            $table->enum('who_can_view_profile',['anyone','followers','community','justme'])->default('anyone');
            $table->string('who_can_connect')->nullable();
            $table->enum('private_messages',['0','1'])->default('1');
            $table->enum('when_someone_request_to_follow',['0','1'])->default('1');
            $table->enum('weekly_updates',['0','1'])->default('1');

            $table->text('address')->nullable();
            $table->string('lattitude')->nullable();
            $table->string('longitude')->nullable();
            $table->string('website')->nullable();
            $table->string('fb_link')->nullable();
            $table->longText('about')->nullable();
            $table->string('company_name')->nullable();
            $table->string('restaurant_name')->nullable();
            $table->string('vat_no')->nullable();
            $table->string('fda_no')->nullable();

            $table->rememberToken();
            $table->string('timezone')->nullable();
            $table->string('locale')->default("en");
            $table->integer('profile_percentage')->default("25");
            $table->softDeletes();
            $table->integer('created_by')->default(0);
            $table->integer('updated_by')->default(0);
            $table->enum('is_visited_marketplace',['1','0'])->default('0')->comment("0=not visited, 1=visited");
            $table->tinyInteger('notification_status',['1','0'])->default('1')->comment("0=Off, 1=On");
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
        Schema::dropIfExists('users');
    }
}
