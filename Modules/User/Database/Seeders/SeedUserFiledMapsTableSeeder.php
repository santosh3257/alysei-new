<?php

namespace Modules\User\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use DB;
class SeedUserFiledMapsTableSeeder extends Seeder
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
                        /*array("user_field_id"=>"26",'option_id'=>'667','child_id'=>'26'),
                        array("user_field_id"=>"24",'option_id'=>'602','child_id'=>'24'),
                        array("user_field_id"=>"21",'option_id'=>'630','child_id'=>'21'),
                        array("user_field_id"=>"22",'option_id'=>'631','child_id'=>'22'),
                        array("user_field_id"=>"8",'option_id'=>'601','child_id'=>'8'),*/


                        array("user_field_id"=>"26",'option_id'=>'602','role_id'=>'10'),
                        array("user_field_id"=>"24",'option_id'=>'602','role_id'=>'7'),
                        array("user_field_id"=>"24",'option_id'=>'602','role_id'=>'8'),
                        /*array("user_field_id"=>"21",'option_id'=>'630','role_id'=>'9'),
                        array("user_field_id"=>"22",'option_id'=>'631','role_id'=>'9'),*/
                        array("user_field_id"=>"21",'option_id'=>'628','role_id'=>'9'),
                        array("user_field_id"=>"22",'option_id'=>'629','role_id'=>'9'),
                        array("user_field_id"=>"8",'option_id'=>'601','role_id'=>'10'),
                    );

        foreach ($data as $key => $value) {
            DB::table('user_field_maps')->insert($value);
        }
    }
}
