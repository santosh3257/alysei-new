<?php

namespace Modules\User\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\User\Entities\Hub;

class HubTableSeeder extends Seeder
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
                        array("country_id" => "231", "state_id" => "3931", "title" => "US Hub"),
                        array("country_id" => "107", "state_id" => "1821", "title" => "Italian Hub")
                    );

        foreach ($data as $key => $value) {
            $db = Hub::create($value);     
        }
    }
}
