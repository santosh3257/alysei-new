<?php

namespace Modules\Activity\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Activity\Entities\MapPermissionRole;

class SeedMapPermissionRolesTableTableSeeder extends Seeder
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
                        //producer
                        array("connect_follow_permission_id" => "1", "role_id" => "3"),
                        array("connect_follow_permission_id" => "1", "role_id" => "4"),
                        array("connect_follow_permission_id" => "1", "role_id" => "5"),
                        array("connect_follow_permission_id" => "1", "role_id" => "6"),
                        array("connect_follow_permission_id" => "1", "role_id" => "7"),
                        array("connect_follow_permission_id" => "1", "role_id" => "8"),
                        array("connect_follow_permission_id" => "1", "role_id" => "9"),

                        //importer
                        array("connect_follow_permission_id" => "2", "role_id" => "3"),
                        array("connect_follow_permission_id" => "2", "role_id" => "4"),
                        array("connect_follow_permission_id" => "2", "role_id" => "5"),
                        array("connect_follow_permission_id" => "2", "role_id" => "6"),
                        array("connect_follow_permission_id" => "2", "role_id" => "7"),
                        array("connect_follow_permission_id" => "2", "role_id" => "8"),
                        array("connect_follow_permission_id" => "2", "role_id" => "9"),

                        //distributor
                        array("connect_follow_permission_id" => "3", "role_id" => "3"),
                        array("connect_follow_permission_id" => "3", "role_id" => "4"),
                        array("connect_follow_permission_id" => "3", "role_id" => "5"),
                        array("connect_follow_permission_id" => "3", "role_id" => "6"),
                        array("connect_follow_permission_id" => "3", "role_id" => "7"),
                        array("connect_follow_permission_id" => "3", "role_id" => "8"),
                        array("connect_follow_permission_id" => "3", "role_id" => "9"),

                        //importer & distributor
                        array("connect_follow_permission_id" => "4", "role_id" => "3"),
                        array("connect_follow_permission_id" => "4", "role_id" => "4"),
                        array("connect_follow_permission_id" => "4", "role_id" => "5"),
                        array("connect_follow_permission_id" => "4", "role_id" => "6"),
                        array("connect_follow_permission_id" => "4", "role_id" => "7"),
                        array("connect_follow_permission_id" => "4", "role_id" => "8"),
                        array("connect_follow_permission_id" => "4", "role_id" => "9"),

                        //voice of expert
                        array("connect_follow_permission_id" => "5", "role_id" => "3"),
                        array("connect_follow_permission_id" => "5", "role_id" => "4"),
                        array("connect_follow_permission_id" => "5", "role_id" => "5"),
                        array("connect_follow_permission_id" => "5", "role_id" => "6"),
                        array("connect_follow_permission_id" => "5", "role_id" => "7"),
                        array("connect_follow_permission_id" => "5", "role_id" => "8"),
                        array("connect_follow_permission_id" => "5", "role_id" => "9"),

                        //travel agencies
                        array("connect_follow_permission_id" => "6", "role_id" => "3"),
                        array("connect_follow_permission_id" => "6", "role_id" => "4"),
                        array("connect_follow_permission_id" => "6", "role_id" => "5"),
                        array("connect_follow_permission_id" => "6", "role_id" => "6"),
                        array("connect_follow_permission_id" => "6", "role_id" => "7"),
                        array("connect_follow_permission_id" => "6", "role_id" => "8"),
                        array("connect_follow_permission_id" => "6", "role_id" => "9"),

                        //restaurants
                        array("connect_follow_permission_id" => "7", "role_id" => "3"),
                        array("connect_follow_permission_id" => "7", "role_id" => "4"),
                        array("connect_follow_permission_id" => "7", "role_id" => "5"),
                        array("connect_follow_permission_id" => "7", "role_id" => "6"),
                        array("connect_follow_permission_id" => "7", "role_id" => "7"),
                        array("connect_follow_permission_id" => "7", "role_id" => "8"),
                        array("connect_follow_permission_id" => "7", "role_id" => "9"),

                        //voygers can connect with voygers
                        array("connect_follow_permission_id" => "8", "role_id" => "10"),

                        //voygers can follow members
                        array("connect_follow_permission_id" => "9", "role_id" => "3"),
                        array("connect_follow_permission_id" => "9", "role_id" => "4"),
                        array("connect_follow_permission_id" => "9", "role_id" => "5"),
                        array("connect_follow_permission_id" => "9", "role_id" => "6"),
                        array("connect_follow_permission_id" => "9", "role_id" => "7"),
                        array("connect_follow_permission_id" => "9", "role_id" => "8"),
                        array("connect_follow_permission_id" => "9", "role_id" => "9")

                    );

        foreach ($data as $key => $value) {
            $db = MapPermissionRole::create($value);     
        }
    }
}
