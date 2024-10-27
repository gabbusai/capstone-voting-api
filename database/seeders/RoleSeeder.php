<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('roles')->insert([
            ['id' => 1, 'name' => 'Student', 'description' => 'Student Voter'],
            ['id' => 2, 'name' => 'Candidate', 'description' => 'Student Council Candidate'],
            ['id' => 3, 'name' => 'Admin', 'description' => 'System Administrator'],
        ]);
    }
}
