<?php

namespace Modules\User\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use DB;

class SeedFeaturedListingTypeRoleMapsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 1   Product
        // 2   Recipie
        // 3   Blog
        // 4   Package
        // 5   Event

        Model::unguard();

        $data = array(
                        array("featured_listing_type_id"=>1,'role_id'=>3),
                        array("featured_listing_type_id"=>1,'role_id'=>4),
                        array("featured_listing_type_id"=>1,'role_id'=>5),
                        array("featured_listing_type_id"=>1,'role_id'=>6),
                        array("featured_listing_type_id"=>3,'role_id'=>7),
                        array("featured_listing_type_id"=>4,'role_id'=>8),
                        array("featured_listing_type_id"=>2,'role_id'=>9),

                        array("featured_listing_type_id"=>5,'role_id'=>9),
                        array("featured_listing_type_id"=>6,'role_id'=>7),
                        array("featured_listing_type_id"=>7,'role_id'=>8),
                    );

        foreach ($data as $key => $value) {
            DB::table('featured_listing_type_role_maps')->insert($value);
        }
    }
}
