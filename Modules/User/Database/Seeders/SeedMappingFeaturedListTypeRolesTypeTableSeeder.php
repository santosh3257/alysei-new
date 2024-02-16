<?php

namespace Modules\User\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class SeedMappingFeaturedListTypeRolesTypeTableSeeder extends Seeder
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
                        array("featured_list_type_id" => "1","role_id"=>"3"),
                        array("featured_list_type_id" => "1","role_id"=>"4"),
                        array("featured_list_type_id" => "1","role_id"=>"5"),
                        array("featured_list_type_id" => "1","role_id"=>"6"),
                        array("featured_list_type_id" => "3","role_id"=>"7"),
                        array("featured_list_type_id" => "2","role_id"=>"9"),
                        array("featured_list_type_id" => "4","role_id"=>"8")
                    );

        foreach ($data as $key => $value) {
            DB::table('featured_listing_role_maps')->insert($value);
        }
    }
}
