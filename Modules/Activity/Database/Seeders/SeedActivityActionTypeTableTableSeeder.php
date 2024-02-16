<?php

namespace Modules\Activity\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class SeedActivityActionTypeTableTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();
        $data = array(
            array("type" => "comment_activity_action","body" => "{item:@subject} commented on {item:@owner}'s {item:@object:post}."),

            /*array("type" => "comment_user","module"=>"user","body"=>"{item:$subject} commented on {item:$owner}'s profile: {body:$body}"),

            array("type" => "cover_photo_update","module"=>"user","body"=>"{item:$subject} has added a new cover photo."),

            array("type" => "friends","module"=>"user","body"=>"{item:$subject} is now friends with {item:$object}."),

            array("type" => "friends_follow","module"=>"user","body"=>"{item:$subject} is now following {item:$object}."),

            array("type" => "like_activity_action","module"=>"activity","body"=>"{item:$subject} liked {item:$owner}'s {item:$object:post}."),

            array("type" => "login","module"=>"user","body"=>"{item:$subject} has signed in."),

            array("type" => "logout","module"=>"user","body"=>"{item:$subject} has signed out."),

            array("type" => "network_join","module"=>"network","body"=>"{item:$subject} joined the network {item:$object}"),

            array("type" => "post","module"=>"user","body"=>"{actors:$subject:$object}: {body:$body}"),

            array("type" => "post_self","module"=>"user","body"=>"{item:$subject} {body:$body}"),

            array("type" => "profile_photo_update","module"=>"user","body"=>"{item:$subject} has added a new profile photo."),

            array("type" => "share","module"=>"user","activity"=>"{item:$subject} shared {item:$object}'s {var:$type}. {body:$body}"),

            array("type" => "signup","module"=>"user","activity"=>"{item:$subject} has just signed up. Say hello!"),

            array("type" => "status","module"=>"user","activity"=>"{item:$subject} {body:$body}"),

            array("type" => "tagged","module"=>"user","activity"=>"{item:$subject} tagged {item:$object} in a {var:$label}:"),*/
                        
        );

        foreach ($data as $key => $value) {
            DB::table('activity_action_types')->insert($value);
        }
       
    }
}
