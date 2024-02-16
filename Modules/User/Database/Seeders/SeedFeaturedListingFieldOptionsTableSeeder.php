<?php

namespace Modules\User\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use DB;

class SeedFeaturedListingFieldOptionsTableSeeder extends Seeder
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
                        array("featured_listing_field_id" => 7,"option"=>"Adventure"),
                        array("featured_listing_field_id" => 7,"option"=>"Tech"),
                        array("featured_listing_field_id" => 7,"option"=>"Family"),
                        array("featured_listing_field_id" => 7,"option"=>"Wellness"),
                        array("featured_listing_field_id" => 7,"option"=>"Fitness"),
                        array("featured_listing_field_id" => 7,"option"=>"Photography"),
                        array("featured_listing_field_id" => 7,"option"=>"Food & Drink"),
                        array("featured_listing_field_id" => 7,"option"=>"Writing"),
                        array("featured_listing_field_id" => 7,"option"=>"Culture"),
                        
                    );

        foreach ($data as $key => $value) {
            DB::table('featured_listing_field_options')->insert($value);
        }
        // $this->call("OthersTableSeeder");
    }
}
