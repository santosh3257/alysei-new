<?php

namespace Modules\User\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class UserDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $this->call(SeedRolesTableSeeder::class);
        $this->call(SeedUserFieldMapRolesTableSeeder::class);
        $this->call(SeedUserFieldOptionsTableSeeder::class);
        $this->call(SeedUserFieldsTableSeeder::class);
        $this->call(SeedWalkThroughScreensTableSeeder::class);
        $this->call(SeedUserFiledMapsTableSeeder::class);
        
    }
}
