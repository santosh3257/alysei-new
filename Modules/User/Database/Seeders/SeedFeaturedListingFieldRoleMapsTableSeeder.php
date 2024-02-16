<?php

namespace Modules\User\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use DB;

class SeedFeaturedListingFieldRoleMapsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //featured_listing_type_id
        // 1   Product
        // 2   Recipie
        // 3   Blog
        // 4   Package
        // 5   Event
        Model::unguard();

        $data = array(
                        //Producers
                        array("featured_listing_type_id"=>1,"featured_listing_field_id"=>1,'role_id'=>3,'order'=>1),
                        array("featured_listing_type_id"=>1,"featured_listing_field_id"=>2,'role_id'=>3,'order'=>2),
                        array("featured_listing_type_id"=>1,"featured_listing_field_id"=>3,'role_id'=>3,'order'=>3),
                        //array("featured_listing_type_id"=>1,"featured_listing_field_id"=>10,'role_id'=>3,'order'=>4),
                        array("featured_listing_type_id"=>1,"featured_listing_field_id"=>8,'role_id'=>3,'order'=>4),

                        //Importers
                        array("featured_listing_type_id"=>1,"featured_listing_field_id"=>1,'role_id'=>4,'order'=>1),
                        array("featured_listing_type_id"=>1,"featured_listing_field_id"=>2,'role_id'=>4,'order'=>2),
                        array("featured_listing_type_id"=>1,"featured_listing_field_id"=>3,'role_id'=>4,'order'=>3),
                        array("featured_listing_type_id"=>1,"featured_listing_field_id"=>8,'role_id'=>4,'order'=>4),
                        //array("featured_listing_type_id"=>1,"featured_listing_field_id"=>10,'role_id'=>4,'order'=>4),

                        //Distributors
                        array("featured_listing_type_id"=>1,"featured_listing_field_id"=>1,'role_id'=>5,'order'=>1),
                        array("featured_listing_type_id"=>1,"featured_listing_field_id"=>2,'role_id'=>5,'order'=>2),
                        array("featured_listing_type_id"=>1,"featured_listing_field_id"=>3,'role_id'=>5,'order'=>3),
                        array("featured_listing_type_id"=>1,"featured_listing_field_id"=>8,'role_id'=>5,'order'=>4),
                        //array("featured_listing_type_id"=>1,"featured_listing_field_id"=>10,'role_id'=>5,'order'=>4),

                        //Importers & Distributors
                        array("featured_listing_type_id"=>1,"featured_listing_field_id"=>1,'role_id'=>6,'order'=>1),
                        array("featured_listing_type_id"=>1,"featured_listing_field_id"=>2,'role_id'=>6,'order'=>2),
                        array("featured_listing_type_id"=>1,"featured_listing_field_id"=>3,'role_id'=>6,'order'=>3),
                        array("featured_listing_type_id"=>1,"featured_listing_field_id"=>8,'role_id'=>6,'order'=>4),
                        //array("featured_listing_type_id"=>1,"featured_listing_field_id"=>10,'role_id'=>6,'order'=>4),

                        //Voice Of Expert
                        array("featured_listing_type_id"=>3,"featured_listing_field_id"=>1,'role_id'=>7,'order'=>1),
                        array("featured_listing_type_id"=>3,"featured_listing_field_id"=>2,'role_id'=>7,'order'=>2),
                        array("featured_listing_type_id"=>3,"featured_listing_field_id"=>3,'role_id'=>7,'order'=>3),
                        array("featured_listing_type_id"=>3,"featured_listing_field_id"=>8,'role_id'=>7,'order'=>4),

                        array("featured_listing_type_id"=>6,"featured_listing_field_id"=>1,'role_id'=>7,'order'=>1),
                        array("featured_listing_type_id"=>6,"featured_listing_field_id"=>2,'role_id'=>7,'order'=>2),
                        array("featured_listing_type_id"=>6,"featured_listing_field_id"=>8,'role_id'=>7,'order'=>3),
                        array("featured_listing_type_id"=>6,"featured_listing_field_id"=>8,'role_id'=>7,'order'=>4),

                        //Travel Agencies
                        array("featured_listing_type_id"=>4,"featured_listing_field_id"=>1,'role_id'=>8,'order'=>1),
                        array("featured_listing_type_id"=>4,"featured_listing_field_id"=>2,'role_id'=>8,'order'=>2),
                        array("featured_listing_type_id"=>4,"featured_listing_field_id"=>3,'role_id'=>8,'order'=>3),
                        array("featured_listing_type_id"=>4,"featured_listing_field_id"=>8,'role_id'=>8,'order'=>4),
                        
                        array("featured_listing_type_id"=>7,"featured_listing_field_id"=>11,'role_id'=>8,'order'=>1),
                        array("featured_listing_type_id"=>7,"featured_listing_field_id"=>12,'role_id'=>8,'order'=>2),
                        array("featured_listing_type_id"=>7,"featured_listing_field_id"=>13,'role_id'=>8,'order'=>3),
                        array("featured_listing_type_id"=>7,"featured_listing_field_id"=>14,'role_id'=>8,'order'=>4),
                        array("featured_listing_type_id"=>7,"featured_listing_field_id"=>15,'role_id'=>8,'order'=>5),
                        array("featured_listing_type_id"=>7,"featured_listing_field_id"=>16,'role_id'=>8,'order'=>6),
                        array("featured_listing_type_id"=>7,"featured_listing_field_id"=>1,'role_id'=>8,'order'=>7),
                        array("featured_listing_type_id"=>7,"featured_listing_field_id"=>8,'role_id'=>8,'order'=>8),
                        

                        //Restaurants
                        array("featured_listing_type_id"=>2,"featured_listing_field_id"=>1,'role_id'=>9,'order'=>1),
                        array("featured_listing_type_id"=>2,"featured_listing_field_id"=>2,'role_id'=>9,'order'=>2),
                        array("featured_listing_type_id"=>2,"featured_listing_field_id"=>3,'role_id'=>9,'order'=>3),
                        array("featured_listing_type_id"=>2,"featured_listing_field_id"=>8,'role_id'=>9,'order'=>4),
                        
                        array("featured_listing_type_id"=>5,"featured_listing_field_id"=>17,'role_id'=>9,'order'=>1),
                        array("featured_listing_type_id"=>5,"featured_listing_field_id"=>10,'role_id'=>9,'order'=>2),
                        array("featured_listing_type_id"=>5,"featured_listing_field_id"=>5,'role_id'=>9,'order'=>3),
                        array("featured_listing_type_id"=>5,"featured_listing_field_id"=>6,'role_id'=>9,'order'=>4),
                        array("featured_listing_type_id"=>5,"featured_listing_field_id"=>7,'role_id'=>9,'order'=>5),
                        array("featured_listing_type_id"=>5,"featured_listing_field_id"=>1,'role_id'=>9,'order'=>6),
                        array("featured_listing_type_id"=>5,"featured_listing_field_id"=>8,'role_id'=>9,'order'=>7),

                        //Restaurants
                        /*array("featured_listing_type_id"=>5,"featured_listing_field_id"=>1,'role_id'=>9,'order'=>1),
                        array("featured_listing_type_id"=>5,"featured_listing_field_id"=>2,'role_id'=>9,'order'=>2),
                        array("featured_listing_type_id"=>5,"featured_listing_field_id"=>3,'role_id'=>9,'order'=>3),
                        array("featured_listing_type_id"=>5,"featured_listing_field_id"=>5,'role_id'=>9,'order'=>4),
                        array("featured_listing_type_id"=>5,"featured_listing_field_id"=>6,'role_id'=>9,'order'=>5),
                        array("featured_listing_type_id"=>5,"featured_listing_field_id"=>7,'role_id'=>9,'order'=>6),*/

                    );

        foreach ($data as $key => $value) {
            DB::table('featured_listing_field_role_maps')->insert($value);
        }
    }
}
