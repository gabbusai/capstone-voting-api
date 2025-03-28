<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('departments')->insert([
            ['id' => 1, 'name' => 'CCIS'],
            ['id' => 2, 'name' => 'CON'],
            ['id' => 3, 'name' => 'COB'],
            ['id' => 4, 'name' => 'CHTM'],
            ['id' => 5, 'name' => 'CCM'],
            ['id' => 6, 'name' => 'COE'],
            ['id' => 7, 'name' => 'CASSED'],
            ['id' => 8, 'name' => 'COC'],
        ]);
    }
}
