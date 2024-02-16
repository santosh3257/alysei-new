<?php

namespace Modules\User\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use DB;

class SeedFeaturedListingFieldsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
       $data = array(
                        array("title"=>"Image",'type'=>'file','name'=>'image_id','required'=>'yes'),
                        array("title"=>"Title",'type'=>'text','name'=>'title','required'=>'yes'),
                        
                        array("title"=>"URL",'type'=>'url','name'=>'listing_url','required'=>'no'),

                        array("title"=>"Recipe Type",'type'=>'text','name'=>'recipe_type','required'=>'yes'),

                        array("title"=>"Location",'type'=>'map','name'=>'location','required'=>'yes'),

                        array("title"=>"Date",'type'=>'date','name'=>'date','required'=>'yes'),

                        array("title"=>"Time",'type'=>'time','name'=>'time','required'=>'yes'),

                        //array("title"=>"Event Type",'type'=>'select','name'=>'event_type','required'=>'yes'),

                        //array("title"=>"Package Link",'type'=>'url','name'=>'package_link','required'=>'yes'),

                        //array("title"=>"Blog Link",'type'=>'url','name'=>'blog_link','required'=>'yes'),

                        array("title"=>"Description",'type'=>'text','name'=>'description','required'=>'no'),
                        array("title"=>"Tags",'type'=>'text','name'=>'tags','required'=>'yes'),
                        array("title"=>"Host Name",'type'=>'text','name'=>'host_name','required'=>'yes'),
                        array("title"=>"Package Name",'type'=>'text','name'=>'package_name','required'=>'yes'),
                        array("title"=>"Travel Agency",'type'=>'text','name'=>'travel_agency','required'=>'yes'),
                        array("title"=>"Region",'type'=>'text','name'=>'region','required'=>'yes'),
                        array("title"=>"Duration",'type'=>'text','name'=>'duration','required'=>'yes'),
                        array("title"=>"Intensity",'type'=>'select','name'=>'intensity','required'=>'yes'),
                        array("title"=>"Price",'type'=>'text','name'=>'price','required'=>'yes'),

                        array("title"=>"Event Name",'type'=>'text','name'=>'event_name','required'=>'yes'),
                        //Sarray("title"=>"Host Name",'type'=>'text','name'=>'host_name','required'=>'yes'),

                        
                        

                    );

        foreach ($data as $key => $value) {
            DB::table('featured_listing_fields')->insert($value);
        }
    }
}

