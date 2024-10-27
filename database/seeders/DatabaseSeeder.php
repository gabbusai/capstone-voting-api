<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            DepartmentSeeder::class,
            PositionSeeder::class,
            StudentSeeder::class,
            ElectionTypeSeeder::class,
            ElectionSeeder::class,
            UserSeeder::class,
            PartListSeeder::class,
            CandidateSeeder::class,
        ]);
    }
}
