<?php

namespace Modules\User\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use DB;

class SeedFeaturedListTypeTableSeeder extends Seeder
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
                        array("title" => "Featured Products","slug"=>"product","position"=>"featured"),
                        array("title" => "Featured Menu","slug"=>"recipie","position"=>"featured"), 
                        array("title" => "Featured","slug"=>"Blog","position"=>"featured"), 
                        array("title" => "Featured Packages","slug"=>"package","position"=>"featured"),
                        array("title" => "Featured Events","slug"=>"events","position"=>"tabs"),
                        array("title" => "Featured Blogs","slug"=>"blogs","position"=>"tabs"),
                        array("title" => "Featured Trips","slug"=>"trips","position"=>"tabs")

                    );

        foreach ($data as $key => $value) {
            DB::table('featured_listing_types')->insert($value);
        }
    }
}
