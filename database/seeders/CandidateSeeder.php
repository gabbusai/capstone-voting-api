<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CandidateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('candidates')->insert([
            [
                'student_id' => 1, // LeBron McPresident
                'user_id' => 1, // Corresponding user ID in users table
                'election_id' => 2,
                'department_id' => 1, // CCIS
                'position_id' => 6, // Assuming 6 is a valid position ID in the positions table
                'party_list_id' => 1, // Assuming 1 is a valid party list ID
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'student_id' => 2, // Donald Junk
                'user_id' => 2, // Corresponding user ID in users table
                'election_id' => 2,
                'department_id' => 1, // CCIS
                'position_id' => 6, // Assuming 6 is a valid position ID in the positions table
                'party_list_id' => 2, // Assuming 2 is a valid party list ID
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'student_id' => 3, // Joe Mama
                'user_id' => 3, // Corresponding user ID in users table
                'election_id' => 1,
                'department_id' => 3, // COB (Used for SSC)
                'position_id' => 1, // Assuming 1 is a valid SSC position ID
                'party_list_id' => 1, // Assuming 1 is a valid party list ID
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'student_id' => 4, // Kowala Ares
                'user_id' => 4, // Corresponding user ID in users table
                'election_id' => 1,
                'department_id' => 4, // CHTM (Used for SSC)
                'position_id' => 1, // Assuming 1 is a valid SSC position ID
                'party_list_id' => 2, // Assuming 2 is a valid party list ID
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
