<?php

namespace Modules\Activity\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Activity\Entities\ConnectFollowPermission;

class SeedConnectFollowPermisssionTableTableSeeder extends Seeder
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
                        array("role_id" => "3", "permission_type" => "1"),
                        array("role_id" => "4", "permission_type" => "1"),
                        array("role_id" => "5", "permission_type" => "1"),
                        array("role_id" => "6", "permission_type" => "1"),
                        array("role_id" => "7", "permission_type" => "1"),
                        array("role_id" => "8", "permission_type" => "1"),
                        array("role_id" => "9", "permission_type" => "1"),
                        array("role_id" => "10", "permission_type" => "1"),
                        array("role_id" => "10", "permission_type" => "2")
                    );

        foreach ($data as $key => $value) {
            $db = ConnectFollowPermission::create($value);     
        }
    }
}
