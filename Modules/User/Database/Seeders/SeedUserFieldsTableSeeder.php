<?php

namespace Modules\User\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use DB;

class SeedUserFieldsTableSeeder extends Seeder
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
                        array("title"=>"Company name","placeholder"=>"Enter company name",'type'=>'text','name'=>'company_name','required'=>'yes'),

                        array("title"=>"Product type","placeholder"=>"Select product type",'type'=>'checkbox','name'=>'product_type','required'=>'yes','hint'=>'Select Product Type','require_update'=>'true'),

                        array("title"=>"Italian Regions","placeholder"=>"Italian regions",'type'=>'select','name'=>'italian_regions','required'=>'yes','display_on_registration'=>'false'),

                        array("title"=>"Horeca","placeholder"=>"Select horeca",'type'=>'radio','name'=>'horeca','required'=>'yes','hint'=>'Select Horeca','require_update'=>'true','display_on_dashboard'=>'true'),

                        array("title"=>"Private Label","placeholder"=>"Private label",'type'=>'radio','name'=>'private_label','required'=>'yes','hint'=>'Select Private Label','require_update'=>'true','display_on_dashboard'=>'true'),

                        array("title"=>"Alysei Brand Label","placeholder"=>"Alysei brand label",'type'=>'radio','name'=>'alysei_brand_label','required'=>'no','hint'=>'Select Alysei Brand Label','require_update'=>'true','display_on_dashboard'=>'true'),

                        array("title"=>"Restaurant name","placeholder"=>"Restaurant name",'type'=>'text','name'=>'restaurant_name','required'=>'yes'),

                        array("title"=>"Hub","placeholder"=>"Hub",'type'=>'select','name'=>'hub','required'=>'yes','require_update'=>'false','display_on_registration'=>'false'),

                        array("title"=>"Provide Pick Up And/Or Delivery","placeholder"=>"Select pick up or delivery",'type'=>'checkbox','name'=>'pick_and_delivery_option','required'=>'no','multiple_option'=>'true','hint'=>'Select Pick up or Delivery','require_update'=>'true'),

                        array("title"=>"Restaurant Type","placeholder"=>"Restaurant type",'type'=>'select','name'=>'restaurant_type','required'=>'yes','require_update'=>'true','display_on_dashboard'=>'true'),

                        array("title"=>"Select your specialization","placeholder"=>"Your specialization",'type'=>'multiselect','name'=>'expertise','required'=>'yes','hint'=>'Choose your specialization','require_update'=>'true','display_on_dashboard'=>'true'),

                        array("title"=>"Title","placeholder"=>"Title",'type'=>'multiselect','name'=>'title','required'=>'yes','require_update'=>'true','display_on_dashboard'=>'true'),

                        array("title"=>"Country","placeholder"=>"Country",'type'=>'select','name'=>'country','required'=>'yes','api_call'=>'true','require_update'=>'false','display_on_dashboard'=>'true'),

                        array("title"=>"Speciality Trips","placeholder"=>"Speciality trips",'type'=>'multiselect','name'=>'speciality','required'=>'yes','multiple_option'=>'true','hint'=>'Select your speciality trips','require_update'=>'true','display_on_dashboard'=>'true'),

                        array("title"=>"Zip/Postal Code","placeholder"=>"Zip/Postal",'type'=>'text','name'=>'zip_postal_code','required'=>'yes'),

                        array("title"=>"Email","placeholder"=>"Your email",'type'=>'email','name'=>'email','required'=>'yes'),

                        array("title"=>"Password","placeholder"=>"Enter password",'type'=>'password','name'=>'password','required'=>'yes','hint'=>'Password must be at least 8 characters and contain at least one numeric digit and a special character.'),

                        array("title"=>"First Name","placeholder"=>"Enter first name",'type'=>'text','name'=>'first_name','required'=>'yes'),

                        array("title"=>"Last Name","placeholder"=>"Enter last name",'type'=>'text','name'=>'last_name','required'=>'yes'),

                        array("title"=>"Country","placeholder"=>"Select",'type'=>'select','name'=>'country','required'=>'yes','multiple_option' => 'true'),

                        array("title"=>"Pick Up Disocunt For Alysei Voyagers","placeholder"=>"Pick up discount for alysei voygers",'type'=>'select','name'=>'pick_up_discount_for_alysei_voyagers','required'=>'no','conditional'=>'no','hint'=>'Select One','require_update'=>'true'),

                        array("title"=>"Delivery Disocunt For Alysei Voyagers","placeholder"=>"Delivery discount for alysei voygers",'type'=>'select','name'=>'delivery_discount_for_alysei_voyagers','required'=>'no','conditional'=>'no','require_update'=>'true'),

                        array("title"=>"Neighborhood","placeholder"=>"Enter neighborhood",'type'=>'select','name'=>'neighborhood','required'=>'no','conditional'=>'yes','require_update'=>'false','display_on_registration'=>'false'),

                        array("title"=>"Region","placeholder"=>"Select",'type'=>'select','name'=>'region','required'=>'no','conditional'=>'no','display_on_registration'=>'false'),

                        array("title"=>"I AGREE TO THE DATA COLLECTION POLICIES STATED IN THE <a href='https://social.alysei.com/privacy-policy'>PRIVACY POLICY</a> AND <a href='https://social.alysei.com/terms'>TERMS OF SERVICE.</a>",'type'=>'terms','name'=>'terms_and_condition','required'=>'yes','conditional'=>'no'),

                        array("title"=>"Italian Regions","placeholder"=>"Select",'type'=>'select','name'=>'italian_regions','required'=>'no','conditional'=>'no','display_on_registration'=>'false'),

                        array("title"=>"Interests","placeholder"=>"Select interests",'type'=>'multiselect','name'=>'interests','required'=>'yes','conditional'=>'no'),

                        
                        array("title"=>"State/Region","placeholder"=>"Select",'type'=>'select','name'=>'state','required'=>'yes','conditional'=>'no','api_call'=>'true','display_on_dashboard'=>'true'),

                        array("title"=>"City","placeholder"=>"Select",'type'=>'select','name'=>'city','required'=>'yes','conditional'=>'no'),

                        array("title"=>"VAT No.","placeholder"=>"Enter VAT no.",'type'=>'text','name'=>'vat_number','required'=>'yes','conditional'=>'no','hint'=>'Enter VAT no.'),

                        array("title"=>"Restaurant Address","placeholder"=>"Enter address",'type'=>'map','name'=>'address','required'=>'yes','conditional'=>'no'),

                        array("title"=>"Enter Your City","placeholder"=>"Enter city",'type'=>'text','name'=>'enter_your_city','required'=>'no','conditional'=>'no'),

                        array("title"=>"Lattitude",'type'=>'hidden','name'=>'lattitude','required'=>'yes','conditional'=>'no'),

                        array("title"=>"Longitude",'type'=>'hidden','name'=>'longitude','required'=>'yes','conditional'=>'no'),


                        array("title"=>"Our Products",'type'=>'text','name'=>'our_product','required'=>'no','conditional'=>'no','require_update'=>'true','display_on_registration'=>'false','display_on_dashboard'=>'true'),

                        array("title"=>"About",'type'=>'text','name'=>'about','required'=>'no','conditional'=>'no','require_update'=>'true','display_on_registration'=>'false','display_on_dashboard'=>'true'),

                        array("title"=>"Our Menu",'type'=>'text','name'=>'our_menu','required'=>'no','conditional'=>'no','require_update'=>'true','display_on_registration'=>'false','display_on_dashboard'=>'true'),

                        array("title"=>"Our Tours",'type'=>'text','name'=>'our_tour','required'=>'no','conditional'=>'no','require_update'=>'true','display_on_registration'=>'false','display_on_dashboard'=>'true')

                    );

        foreach ($data as $key => $value) {
            DB::table('user_fields')->insert($value);
        }
    }
}
