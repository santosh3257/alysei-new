<?php

namespace Modules\User\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\User\Entities\MapHubCity;

class SeedMapHubCityTableSeeder extends Seeder
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
                    array("hub_id" => "1", "city_id" => "43932"),
                    array("hub_id" => "1", "city_id" => "43923"),
                    array("hub_id" => "2", "city_id" => "22339"),
                    array("hub_id" => "2", "city_id" => "22343")
                    );

        foreach ($data as $key => $value) {
            $db = MapHubCity::create($value);     
        }
    }
}
