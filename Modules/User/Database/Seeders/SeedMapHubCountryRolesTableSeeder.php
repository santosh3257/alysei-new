<?php

namespace Modules\User\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\User\Entities\MapHubCountryRole;

class SeedMapHubCountryRolesTableSeeder extends Seeder
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
                        array("country_id" => "233", "role_id" => "3"),
                        array("country_id" => "233", "role_id" => "6"),
                        array("country_id" => "233", "role_id" => "7"),
                        array("country_id" => "233", "role_id" => "8"),
                        array("country_id" => "233", "role_id" => "4"),
                        array("country_id" => "233", "role_id" => "5"),
                        array("country_id" => "233", "role_id" => "9")
                    );

        foreach ($data as $key => $value) {
            $db = MapHubCountryRole::create($value);     
        }
    }
}
