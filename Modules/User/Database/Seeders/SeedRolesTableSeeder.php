<?php

namespace Modules\User\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\User\Entities\Role;

class SeedRolesTableSeeder extends Seeder
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
                        array("type" => "super_admin","name"=>"Super Admin",'slug'=>'super_admin','display_name'=>'Super Admin','description' => '','order'=>1),
                        array("type" => "admin","name"=>"Admin",'slug'=>'admin','display_name'=>'Admin','description' => '','order'=>2),
                        array("type" => "member","name"=>"Italian F&B Producers",'slug'=>'Italian_F_and_B_Producers','display_name'=>'Producer','description' => 'Explore, find and connect with certified Importers and Distributors in the USA, build up and consolidate your brand, promote your products, and reach your consumers.','order'=>3),
                        array("type" => "member","name"=>"Importer",'slug'=>'importer','display_name'=>'Importer','description' => ''),
                        array("type" => "member","name"=>"Distributer",'slug'=>'distributer','display_name'=>'Distributer','description' => ''),
                        array("type" => "member","name"=>"US Importers & Distributers",'slug'=>'Importer_and_Distributer','display_name'=>'Importers & Distributers','description' => 'Explore, find and connect with local Italian Producers to strength your product portfolio, enhance your competiviness, expand your brand and market access.','order'=>3),
                        array("type" => "member","name"=>"Voice Of Experts",'slug'=>'voice_of_expert','display_name'=>'Voice Of Experts','description' => 'Chefs, Cooking Schools, and all Italian Food and Beverage specialists  will leverage on the Alysei platform to promote their name, brand, offering, events, blogs.','order'=>5),
                        array("type" => "member","name"=>"Travel Agencies",'slug'=>'travel_agencies','display_name'=>'Travel Agencies','description' => 'Strength connection with certified Italian Producers, Importers and Distributors in USA, Voice of Experts, grown your visibility, reach your target customers.','order'=>6),
                        array("type" => "member","name"=>"Italian Restaurants in US",'slug'=>'restaurents','display_name'=>'Italian Restaurants in US','description' => 'Strength collaboration with producers, Importers, promote your cuisine and special events, bring more clients to the table by exponentially expand your reach.','order'=>4),
                        array("type" => "subscription","name"=>"Voyagers",'slug'=>'voyagers','display_name'=>'Voyagers','description' => 'Enjoy the magic world of our endless cuisine searching for products, restaurants, events, trip to Italy, tasting tours, cooking classes, recipes, blogs and much more.','order'=>7)
                    );

        foreach ($data as $key => $value) {
            $db = Role::create($value);     
        }
    }
}
