<?php

namespace Modules\User\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\User\Entities\MapCountryRole;

class SeedMapCountryRolesTableSeeder extends Seeder
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
                        //USA
                        //array("country_id" => "231", "role_id" => "3"),
                        array("country_id" => "233", "role_id" => "6"),
                        array("country_id" => "233", "role_id" => "7"),
                        array("country_id" => "233", "role_id" => "8"),
                        array("country_id" => "233", "role_id" => "9"),
                        //array("country_id" => "231", "role_id" => "10"),
                        //italy
                        array("country_id" => "107", "role_id" => "3"),
                        //array("country_id" => "107", "role_id" => "6"),
                        array("country_id" => "107", "role_id" => "7"),
                        array("country_id" => "107", "role_id" => "8"),
                        //array("country_id" => "107", "role_id" => "9"),
                        //array("country_id" => "107", "role_id" => "10")
                    );

        foreach ($data as $key => $value) {
            $db = MapCountryRole::create($value);     
        }
    }
}
