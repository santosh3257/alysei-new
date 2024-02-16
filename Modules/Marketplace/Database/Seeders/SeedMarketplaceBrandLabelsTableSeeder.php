<?php

namespace Modules\Marketplace\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Marketplace\Entities\MarketplaceBrandLabel;

class SeedMarketplaceBrandLabelsTableSeeder extends Seeder
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
                        array("name" => "Alysei Brand label", "status" => "1"),
                        array("name" => "Private Label", "status" => "1"),
                        array("name" => "Horeca", "status" => "1")
                    );

        foreach ($data as $key => $value) {
            $db = MarketplaceBrandLabel::create($value);     
        }
    }
}
