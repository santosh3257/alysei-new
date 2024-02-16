<?php

namespace Modules\User\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use DB;

class SeedWalkThroughScreensTableSeeder extends Seeder
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
                        array("role_id"=>"3",'step'=>'step1','title'=>'Alysei Certification','description'=>'Sign up, create and complete your Company profile, showcase your feature products, select your Hub in USA, promote your Brand.','order'=>1,'image_id'=>2222),

                        array("role_id"=>"3",'step'=>'step2','title'=>'Access to the B2B Platform','description'=>'Access to the B2B Engine to search and connect with Importers, Distributors, Italian Restaurants in USA, Voice of Expert, Travel Agencies.','order'=>2,'image_id'=>2226),

                        array("role_id"=>"3",'step'=>'step3','title'=>'Your own Market Place','description'=>'Access to the Market Place, create your unique Store, upload your product portfolio, enhance your visibility, expand your reach.','order'=>3,'image_id'=>2227),

                        array("role_id"=>"3",'step'=>'step4','title'=>'From Farm to Fork','description'=>'Full access to Alysei Social Platform to reach your target customers in USA within your Hub, launch target product and event promotion campaigns, strength your Brand.','order'=>4,'image_id'=>2228),

                        array("role_id"=>"6",'step'=>'step1','title'=>'Alysei Certification','description'=>'Sign up, create and complete your Company profile, showcase your feature products, select your Hub in USA, promote your Brand.','order'=>1,'image_id'=>2218),

                        array("role_id"=>"6",'step'=>'step2','title'=>'Access to the B2B Platform','description'=>'Access to the B2B Engine to search and connect with Italian Producers, Italian Restaurants in USS, Voice of Expert, Travel Agencies.','order'=>2,'image_id'=>2219),

                        array("role_id"=>"6",'step'=>'step3','title'=>'Your own Market Place','description'=>'Access to the Market Place, explore, search for high quality Italian producers by Region, Product, Category and much more, portfolio, connect develop business collaboration opportunities, diversify and enrich your product.','order'=>3,'image_id'=>2220),

                        array("role_id"=>"6",'step'=>'step4','title'=>'Gain Market Visibility','description'=>'Full access to Alysei Social Platform to reach your target customers in USA within your Hub, launch target product and event promotion campaigns, strength your Brand.','order'=>4,'image_id'=>2221),


                        array("role_id"=>"7",'step'=>'step1','title'=>'Alysei Certification','description'=>'Sign up, create and complete your Profile, showcase your feature blogs, books, events, projects, select your Hub in USA, promote your Brand and offering.','order'=>1,'image_id'=>2229),

                        array("role_id"=>"7",'step'=>'step2','title'=>'Access to the B2B Platform','description'=>'Enhance your collaboration opportunity by leveraging on the B2B Engine to search and connect with Italian Producers, Importers and Distributors in USA, Italian Restaurants in USA, Travel Agencies.','order'=>2,'image_id'=>2230),

                        array("role_id"=>"7",'step'=>'step3','title'=>'Your own Market Place','description'=>'Access to the Market Place, explore, search for high quality Italian producers by Region, Product, Category and much more, connect develop business collaboration opportunities through your local Importer and Distributor.','order'=>3,'image_id'=>2231),

                        array("role_id"=>"7",'step'=>'step4','title'=>'Expand your reach','description'=>'Full access to Alysei Social Platform to promote to strength your Brand,your blogs, books, events, projects','order'=>4,'image_id'=>2232),

                        array("role_id"=>"8",'step'=>'step1','title'=>'Alysei Certification','description'=>'Sign up, create and complete your Company profile, showcase your feature trips, select your Hub in USA, promote your offering.','order'=>1,'image_id'=>2233),

                        array("role_id"=>"8",'step'=>'step2','title'=>'Access to the B2B Platform','description'=>'Access to the B2B Engine to search and connect with Italian Producers, Importers and Distributors in US, Italian Restaurants in US, Voice of Expert.','order'=>2,'image_id'=>2234),

                        array("role_id"=>"8",'step'=>'step3','title'=>'Your own Market Place','description'=>'Access to the Market Place, explore, search for high quality Italian producers by Region, Product, Category and much more, connect develop business collaboration opportunities.','order'=>3,'image_id'=>2235),

                        array("role_id"=>"8",'step'=>'step4','title'=>'Expand your reach','description'=>'Full access to Alysei Social Platform to define, promote and reach your target market and customer.','order'=>4,'image_id'=>2236),

                        array("role_id"=>"9",'step'=>'step1','title'=>'Alysei Certification','description'=>'Sign up, create and complete your Restaurant profile, showcase your menu and feature recipes, select your Hub in USA, promote your Restaurant.','order'=>1,'image_id'=>2237),

                        array("role_id"=>"9",'step'=>'step2','title'=>'Access to the B2B Platform','description'=>'Access to the B2B Engine to search and connect with Italian Producers, Importers and Distributors in US, Voice of Expert, Travel Agencies.','order'=>2,'image_id'=>2238),

                        array("role_id"=>"9",'step'=>'step3','title'=>'Your own Market Place','description'=>'Access to the Market Place, explore, search for high quality Italian producers by Region, Product, Category and much more, connect develop business collaboration opportunities through your local Importer and Distributor.','order'=>3,'image_id'=>2239),

                        array("role_id"=>"9",'step'=>'step4','title'=>'Expand your reach','description'=>'Full access to Alysei Social Platform to promote your restaurant and cuisine, organize and promote events bringing more clients to the table by exponentially expand your reach.','order'=>4,'image_id'=>2240),

                        array("role_id"=>"10",'step'=>'step1','title'=>'Choose your HUB in USA','description'=>'Sign up, create and complete your profile, access to the Alysei world.','order'=>1,'image_id'=>2241),

                        array("role_id"=>"10",'step'=>'step2','title'=>'Access to the Alysei Social Platform','description'=>'Access to Alysei to search for products, restaurants, events, cooking classes, Recipes, trips to Italy, post, share, comments and much more.','order'=>2,'image_id'=>2242),

                        array("role_id"=>"10",'step'=>'step3','title'=>'Recipe Tool','description'=>'Access to the Alysei Recipe Tool, search, create, post, share, rate recipes with a click of a mouse.','order'=>3,'image_id'=>2243),

                        array("role_id"=>"10",'step'=>'step4','title'=>'Rewards and Benefits','description'=>'Bring more friends an expand your membership and Benefits.','order'=>4,'image_id'=>2244),

                        array("role_id"=>"0",'step'=>'step4','title'=>'','description'=>'Bridge the gap between tradition and modernity, offering endless opportunities to Italian high quality product manufactures to grow and expand their business in USA while maintaining their centennial tradition and identity.
','order'=>4,'image_id'=>2245),

                        array("role_id"=>"0",'step'=>'step4','title'=>'','description'=>'Alysei is the first B2B and B2C Portal for Italian high-quality products in the Food & Beverage sector, designed and developed on a Collaborative Social Portal environment entirely directed to a public enthusiastic for the Made in Italy eno-gastronomy','order'=>4,'image_id'=>2246),

                        array("role_id"=>"0",'step'=>'step4','title'=>'','description'=>'Alysei certified Producers will be able to search and connect with certified Importers and Distributors in the US, build up and consolidate their brand, promote their products and reach their target customers faster to gain visibility and traction in the USA market','order'=>4,'image_id'=>2247),

                        array("role_id"=>"0",'step'=>'step4','title'=>'','description'=>'Alysei will target the entire population with a strong passion to our culture, history and tradition of the Italian cuisine.','order'=>4,'image_id'=>2248),

                        array("role_id"=>"0",'step'=>'step4','title'=>'','description'=>'Users will join Alysei to enjoy the magic world of our endless cuisine searching for products, restaurants, trips to Italy, events and tasting tours, cooking classes, recipes, blogs and many more activities helping to strengthen our great Made in Italy brand in USA.','order'=>4,'image_id'=>2249),
                        

                    );

        foreach ($data as $key => $value) {
            DB::table('walk_through_screens')->insert($value);
        }
    }
}
