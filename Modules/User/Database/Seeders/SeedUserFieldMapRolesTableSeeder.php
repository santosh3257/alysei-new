<?php

namespace Modules\User\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use DB;

class SeedUserFieldMapRolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        Model::unguard();

        // User Field Id as following
        /*
        1   Company name
        2   Product type
        3   Italian Regions
        4   Horeca
        5   Private Label
        6   Alysei Brand Label
        7   Restaurent name
        8   Hub
        9   Provide Pick Up And/Or Delivery
        10  Restaurent Type
        11  Expertise
        12  Title
        13  Country
        14  Speciality
        15  Zip/Postal Code
        16  Email
        17  Password
        18  First Name
        19  Last Name
        20  Country //not used
        */

        $data = array(
                        //Producers
                        array("user_field_id"=>16,'role_id'=>3,'step'=>'step_1','order'=>1,'edit_profile_field_order'=>0),
                        array("user_field_id"=>17,'role_id'=>3,'step'=>'step_1','order'=>2,'edit_profile_field_order'=>0),
                        array("user_field_id"=>1,'role_id'=>3,'step'=>'step_1','order'=>3,'edit_profile_field_order'=>0),
                        array("user_field_id"=>30,'role_id'=>3,'step'=>'step_1','order'=>4,'edit_profile_field_order'=>0),
                        array("user_field_id"=>2,'role_id'=>3,'step'=>'step_1','order'=>5,'edit_profile_field_order'=>1),

                        array("user_field_id"=>13,'role_id'=>3,'step'=>'step_1','order'=>6,'edit_profile_field_order'=>0),
                        array("user_field_id"=>28,'role_id'=>3,'step'=>'step_1','order'=>7,'edit_profile_field_order'=>0),
                        array("user_field_id"=>29,'role_id'=>3,'step'=>'step_1','order'=>8,'edit_profile_field_order'=>0),
                        array("user_field_id"=>32,'role_id'=>3,'step'=>'step_1','order'=>9,'edit_profile_field_order'=>0),

                        //array("user_field_id"=>3,'role_id'=>3,'step'=>'step_1','order'=>9),
                        array("user_field_id"=>4,'role_id'=>3,'step'=>'step_2','order'=>10,'edit_profile_field_order'=>4),
                        array("user_field_id"=>5,'role_id'=>3,'step'=>'step_2','order'=>11,'edit_profile_field_order'=>5),
                        array("user_field_id"=>6,'role_id'=>3,'step'=>'step_2','order'=>12,'edit_profile_field_order'=>6),
                        array("user_field_id"=>25,'role_id'=>3,'step'=>'step_2','order'=>13,'edit_profile_field_order'=>0),

                        array("user_field_id"=>36,'role_id'=>3,'step'=>'step_2','order'=>14,'edit_profile_field_order'=>2),
                        array("user_field_id"=>35,'role_id'=>3,'step'=>'step_2','order'=>15,'edit_profile_field_order'=>3),

                        //Impoters & Distributor
                        array("user_field_id"=>16,'role_id'=>6,'step'=>'step_1','order'=>1,'edit_profile_field_order'=>0),
                        array("user_field_id"=>17,'role_id'=>6,'step'=>'step_1','order'=>2,'edit_profile_field_order'=>0),
                        array("user_field_id"=>1,'role_id'=>6,'step'=>'step_1','order'=>3,'edit_profile_field_order'=>0),
                        array("user_field_id"=>2,'role_id'=>6,'step'=>'step_1','order'=>4,'edit_profile_field_order'=>1),

                        array("user_field_id"=>13,'role_id'=>6,'step'=>'step_1','order'=>5,'edit_profile_field_order'=>0),
                        array("user_field_id"=>28,'role_id'=>6,'step'=>'step_1','order'=>6,'edit_profile_field_order'=>0),
                        array("user_field_id"=>29,'role_id'=>6,'step'=>'step_1','order'=>7,'edit_profile_field_order'=>0),
                        array("user_field_id"=>32,'role_id'=>6,'step'=>'step_1','order'=>8,'edit_profile_field_order'=>0),
                        //array("user_field_id"=>31,'role_id'=>6,'step'=>'step_1','order'=>9),
                        array("user_field_id"=>15,'role_id'=>6,'step'=>'step_1','order'=>9,'edit_profile_field_order'=>0),

                        //array("user_field_id"=>3,'role_id'=>6,'step'=>'step_1','order'=>5),
                        array("user_field_id"=>4,'role_id'=>6,'step'=>'step_2','order'=>10,'edit_profile_field_order'=>4),
                        array("user_field_id"=>5,'role_id'=>6,'step'=>'step_2','order'=>11,'edit_profile_field_order'=>5),
                        array("user_field_id"=>6,'role_id'=>6,'step'=>'step_2','order'=>12,'edit_profile_field_order'=>6),
                        array("user_field_id"=>25,'role_id'=>6,'step'=>'step_2','order'=>13,'edit_profile_field_order'=>0),

                        array("user_field_id"=>36,'role_id'=>6,'step'=>'step_2','order'=>14,'edit_profile_field_order'=>2),
                        array("user_field_id"=>35,'role_id'=>6,'step'=>'step_2','order'=>15,'edit_profile_field_order'=>3),

                        //Impoters
                        array("user_field_id"=>16,'role_id'=>4,'step'=>'step_1','order'=>1),
                        array("user_field_id"=>17,'role_id'=>4,'step'=>'step_1','order'=>2),
                        array("user_field_id"=>1,'role_id'=>4,'step'=>'step_1','order'=>3),
                        array("user_field_id"=>2,'role_id'=>4,'step'=>'step_1','order'=>4,'edit_profile_field_order'=>1),

                        array("user_field_id"=>13,'role_id'=>4,'step'=>'step_1','order'=>5),
                        array("user_field_id"=>28,'role_id'=>4,'step'=>'step_1','order'=>6),
                        array("user_field_id"=>29,'role_id'=>4,'step'=>'step_1','order'=>7),
                        array("user_field_id"=>32,'role_id'=>4,'step'=>'step_1','order'=>8),
                        array("user_field_id"=>15,'role_id'=>4,'step'=>'step_1','order'=>9),

                        //array("user_field_id"=>3,'role_id'=>4,'step'=>'step_1','order'=>5),
                        array("user_field_id"=>4,'role_id'=>4,'step'=>'step_2','order'=>10,'edit_profile_field_order'=>4),
                        array("user_field_id"=>5,'role_id'=>4,'step'=>'step_2','order'=>11,'edit_profile_field_order'=>5),
                        array("user_field_id"=>6,'role_id'=>4,'step'=>'step_2','order'=>12,'edit_profile_field_order'=>6),
                        array("user_field_id"=>25,'role_id'=>4,'step'=>'step_2','order'=>13),
                        array("user_field_id"=>36,'role_id'=>4,'step'=>'step_2','order'=>14,'edit_profile_field_order'=>2),
                        array("user_field_id"=>35,'role_id'=>4,'step'=>'step_2','order'=>15,'edit_profile_field_order'=>3),

                        //Distributor
                        array("user_field_id"=>16,'role_id'=>5,'step'=>'step_1','order'=>1),
                        array("user_field_id"=>17,'role_id'=>5,'step'=>'step_1','order'=>2),
                        array("user_field_id"=>1,'role_id'=>5,'step'=>'step_1','order'=>3),
                        array("user_field_id"=>2,'role_id'=>5,'step'=>'step_1','order'=>4,'edit_profile_field_order'=>1),

                        array("user_field_id"=>13,'role_id'=>5,'step'=>'step_1','order'=>5),
                        array("user_field_id"=>28,'role_id'=>5,'step'=>'step_1','order'=>6),
                        array("user_field_id"=>29,'role_id'=>5,'step'=>'step_1','order'=>7),
                        array("user_field_id"=>32,'role_id'=>5,'step'=>'step_1','order'=>8),
                        array("user_field_id"=>15,'role_id'=>5,'step'=>'step_1','order'=>9),

                        //array("user_field_id"=>3,'role_id'=>5,'step'=>'step_1','order'=>5),
                        array("user_field_id"=>4,'role_id'=>5,'step'=>'step_2','order'=>10,'edit_profile_field_order'=>4),
                        array("user_field_id"=>5,'role_id'=>5,'step'=>'step_2','order'=>11,'edit_profile_field_order'=>5),
                        array("user_field_id"=>6,'role_id'=>5,'step'=>'step_2','order'=>12,'edit_profile_field_order'=>6),
                        array("user_field_id"=>25,'role_id'=>5,'step'=>'step_2','order'=>13),
                        array("user_field_id"=>36,'role_id'=>5,'step'=>'step_2','order'=>14,'edit_profile_field_order'=>2),
                        array("user_field_id"=>35,'role_id'=>5,'step'=>'step_2','order'=>15,'edit_profile_field_order'=>3),

                        //Restaurents
                        array("user_field_id"=>16,'role_id'=>9,'step'=>'step_1','order'=>1,'edit_profile_field_order'=>0),
                        array("user_field_id"=>17,'role_id'=>9,'step'=>'step_1','order'=>2,'edit_profile_field_order'=>0),
                        array("user_field_id"=>7,'role_id'=>9,'step'=>'step_1','order'=>3,'edit_profile_field_order'=>0),
                        //array("user_field_id"=>8,'role_id'=>9,'step'=>'step_1','order'=>4),

                        array("user_field_id"=>13,'role_id'=>9,'step'=>'step_1','order'=>4,'edit_profile_field_order'=>0),
                        array("user_field_id"=>28,'role_id'=>9,'step'=>'step_1','order'=>5,'edit_profile_field_order'=>0),
                        array("user_field_id"=>29,'role_id'=>9,'step'=>'step_1','order'=>6,'edit_profile_field_order'=>0),
                        array("user_field_id"=>32,'role_id'=>9,'step'=>'step_1','order'=>7,'edit_profile_field_order'=>0),

                        array("user_field_id"=>15,'role_id'=>9,'step'=>'step_2','order'=>8,'edit_profile_field_order'=>0),
                        array("user_field_id"=>31,'role_id'=>9,'step'=>'step_2','order'=>9,'edit_profile_field_order'=>0),
                        array("user_field_id"=>33,'role_id'=>9,'step'=>'step_2','order'=>10,'edit_profile_field_order'=>0),
                        array("user_field_id"=>34,'role_id'=>9,'step'=>'step_2','order'=>11,'edit_profile_field_order'=>0),
                        array("user_field_id"=>9,'role_id'=>9,'step'=>'step_2','order'=>12,'edit_profile_field_order'=>1),
                        array("user_field_id"=>21,'role_id'=>9,'step'=>'step_2','order'=>13,'edit_profile_field_order'=>2),
                        array("user_field_id"=>22,'role_id'=>9,'step'=>'step_2','order'=>14,'edit_profile_field_order'=>3),
                        array("user_field_id"=>10,'role_id'=>9,'step'=>'step_2','order'=>15,'edit_profile_field_order'=>4),
                        array("user_field_id"=>25,'role_id'=>9,'step'=>'step_2','order'=>16,'edit_profile_field_order'=>0),

                        //array("user_field_id"=>8,'role_id'=>9,'step'=>'step_2','order'=>0,'edit_profile_field_order'=>0),
                        //array("user_field_id"=>23,'role_id'=>9,'step'=>'step_2','order'=>0,'edit_profile_field_order'=>0), 
                        array("user_field_id"=>36,'role_id'=>9,'step'=>'step_2','order'=>19,'edit_profile_field_order'=>5),
                        array("user_field_id"=>37,'role_id'=>9,'step'=>'step_2','order'=>20,'edit_profile_field_order'=>6),
                                               
                        //Travel Agencies
                        array("user_field_id"=>16,'role_id'=>8,'step'=>'step_1','order'=>1,'edit_profile_field_order'=>0),
                        array("user_field_id"=>17,'role_id'=>8,'step'=>'step_1','order'=>2,'edit_profile_field_order'=>0),
                        array("user_field_id"=>1,'role_id'=>8,'step'=>'step_1','order'=>3,'edit_profile_field_order'=>0),
                        array("user_field_id"=>14,'role_id'=>8,'step'=>'step_1','order'=>4,'edit_profile_field_order'=>1),

                        array("user_field_id"=>13,'role_id'=>8,'step'=>'step_1','order'=>5,'edit_profile_field_order'=>2),
                        array("user_field_id"=>28,'role_id'=>8,'step'=>'step_1','order'=>6,'edit_profile_field_order'=>0),
                        
                        array("user_field_id"=>29,'role_id'=>8,'step'=>'step_1','order'=>7,'edit_profile_field_order'=>0),
                        array("user_field_id"=>32,'role_id'=>8,'step'=>'step_1','order'=>8,'edit_profile_field_order'=>0),
                        array("user_field_id"=>15,'role_id'=>8,'step'=>'step_1','order'=>9,'edit_profile_field_order'=>0),
                        //array("user_field_id"=>31,'role_id'=>8,'step'=>'step_2','order'=>10),

                        //array("user_field_id"=>24,'role_id'=>8,'step'=>'step_2','order'=>9),
                        array("user_field_id"=>25,'role_id'=>8,'step'=>'step_1','order'=>10,'edit_profile_field_order'=>0),
                        array("user_field_id"=>36,'role_id'=>8,'step'=>'step_1','order'=>11,'edit_profile_field_order'=>3),
                        array("user_field_id"=>38,'role_id'=>8,'step'=>'step_1','order'=>12,'edit_profile_field_order'=>4),
                        //Voyagers
                        array("user_field_id"=>16,'role_id'=>10,'step'=>'step_1','order'=>1),
                        array("user_field_id"=>17,'role_id'=>10,'step'=>'step_1','order'=>2),
                        array("user_field_id"=>18,'role_id'=>10,'step'=>'step_1','order'=>3),
                        array("user_field_id"=>19,'role_id'=>10,'step'=>'step_1','order'=>4),

                        array("user_field_id"=>13,'role_id'=>10,'step'=>'step_1','order'=>5),
                        array("user_field_id"=>28,'role_id'=>10,'step'=>'step_1','order'=>6),
                        array("user_field_id"=>29,'role_id'=>10,'step'=>'step_1','order'=>7),
                        array("user_field_id"=>32,'role_id'=>10,'step'=>'step_1','order'=>8),

                        //array("user_field_id"=>26,'role_id'=>10,'step'=>'step_1','order'=>9),
                        //array("user_field_id"=>8,'role_id'=>10,'step'=>'step_2','order'=>9),
                        //array("user_field_id"=>15,'role_id'=>10,'step'=>'step_2','order'=>9),
                        //array("user_field_id"=>27,'role_id'=>10,'step'=>'step_2','order'=>10),
                        array("user_field_id"=>25,'role_id'=>10,'step'=>'step_1','order'=>10),
                        
                        //Voice Of Expert
                        array("user_field_id"=>16,'role_id'=>7,'step'=>'step_1','order'=>1,'edit_profile_field_order'=>0),
                        array("user_field_id"=>17,'role_id'=>7,'step'=>'step_1','order'=>2,'edit_profile_field_order'=>0),
                        array("user_field_id"=>18,'role_id'=>7,'step'=>'step_1','order'=>3,'edit_profile_field_order'=>0),
                        array("user_field_id"=>19,'role_id'=>7,'step'=>'step_1','order'=>4,'edit_profile_field_order'=>0),

                        array("user_field_id"=>13,'role_id'=>7,'step'=>'step_1','order'=>5,'edit_profile_field_order'=>3),
                        array("user_field_id"=>28,'role_id'=>7,'step'=>'step_1','order'=>6,'edit_profile_field_order'=>0),
                        array("user_field_id"=>29,'role_id'=>7,'step'=>'step_1','order'=>7,'edit_profile_field_order'=>0),
                        array("user_field_id"=>32,'role_id'=>7,'step'=>'step_1','order'=>8,'edit_profile_field_order'=>0),

                        array("user_field_id"=>11,'role_id'=>7,'step'=>'step_2','order'=>9,'edit_profile_field_order'=>1),
                        array("user_field_id"=>12,'role_id'=>7,'step'=>'step_2','order'=>10,'edit_profile_field_order'=>2),
                        array("user_field_id"=>24,'role_id'=>7,'step'=>'step_2','order'=>11,'edit_profile_field_order'=>0),
                        array("user_field_id"=>25,'role_id'=>7,'step'=>'step_2','order'=>12,'edit_profile_field_order'=>0),
                        array("user_field_id"=>36,'role_id'=>7,'step'=>'step_2','order'=>13,'edit_profile_field_order'=>4),

                        //Voygers
                        array("user_field_id"=>36,'role_id'=>10,'step'=>'step_1','order'=>0,'edit_profile_field_order'=>1),

                    );

        foreach ($data as $key => $value) {
            DB::table('user_field_map_roles')->insert($value);
        }
    }
}
