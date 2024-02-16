<?php

namespace Modules\Marketplace\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Marketplace\Entities\MarketplaceProductCategory;

class SeedMarketplaceProductCategoriesTableSeeder extends Seeder
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
                        array("name" => "Baby food", "status" => "1"),
                        array("name" => "Bakery & Snacks", "status" => "1"),
                        array("name" => "Baking mixes", "status" => "1"),
                        array("name" => "Base Ingredients", "status" => "1"),
                        array("name" => "Beer", "status" => "1"),
                        array("name" => "Cereals & Legumes", "status" => "1"),
                        array("name" => "Cheese", "status" => "1"),
                        array("name" => "Coffee & Tea", "status" => "1"),
                        array("name" => "Coffee Beans/Pods/Capsules", "status" => "1"),

                        array("name" => "Confectionary & Sweets", "status" => "1"),
                        array("name" => "Condiments", "status" => "1"),
                        array("name" => "Dairy", "status" => "1"),
                        array("name" => "Fruits & Vegetables", "status" => "1"),
                        array("name" => "Gelato", "status" => "1"),
                        array("name" => "Gelato", "status" => "1"),
                        array("name" => "Honey", "status" => "1"),
                        array("name" => "Nuts and seed", "status" => "1"),

                        array("name" => "Meat & Salumi", "status" => "1"),
                        array("name" => "Mushrooms & Truffles", "status" => "1"),
                        array("name" => "Oil & Vinegar", "status" => "1"),
                        array("name" => "Olives", "status" => "1"),
                        array("name" => "Pasta & Rice", "status" => "1"),
                        array("name" => "Pizza Mix", "status" => "1"),
                        array("name" => "Preserves & Sauces", "status" => "1"),
                        array("name" => "Ready Meals/Soups", "status" => "1"),

                        array("name" => "Seafood", "status" => "1"),
                        array("name" => "Spirits", "status" => "1"),
                        array("name" => "Spreads & Jams", "status" => "1"),
                        array("name" => "Juices, Soft Drinks, Water", "status" => "1"),
                        array("name" => "Wine", "status" => "1")
                    );

        foreach ($data as $key => $value) {
            $db = MarketplaceProductCategory::create($value);     
        }
    }
}
