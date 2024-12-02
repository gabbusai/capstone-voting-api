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
        // Define the list of users with their department and election association
        $users = [
            // General Election (SSC Users)
            ['student_id' => 1, 'position_id' => 1, 'department_id' => 1, 'party_list_id' => 1, 'election_id' => 1],
            ['student_id' => 2, 'position_id' => 1, 'department_id' => 2, 'party_list_id' => 2, 'election_id' => 1],
            ['student_id' => 3, 'position_id' => 2, 'department_id' => 3, 'party_list_id' => 1, 'election_id' => 1],
            ['student_id' => 4, 'position_id' => 2, 'department_id' => 4, 'party_list_id' => 2, 'election_id' => 1],
            ['student_id' => 5, 'position_id' => 3, 'department_id' => 5, 'party_list_id' => 1, 'election_id' => 1],
            ['student_id' => 6, 'position_id' => 3, 'department_id' => 1, 'party_list_id' => 2, 'election_id' => 1],
            ['student_id' => 7, 'position_id' => 4, 'department_id' => 2, 'party_list_id' => 1, 'election_id' => 1],
            ['student_id' => 8, 'position_id' => 4, 'department_id' => 3, 'party_list_id' => 2, 'election_id' => 1],
            ['student_id' => 9, 'position_id' => 5, 'department_id' => 4, 'party_list_id' => 1, 'election_id' => 1],
            ['student_id' => 10, 'position_id' => 5, 'department_id' => 3, 'party_list_id' => 2, 'election_id' => 1],
            // CCIS Users (Only for Election 2)
            ['student_id' => 11, 'position_id' => 6, 'department_id' => 1, 'party_list_id' => 1, 'election_id' => 2],
            ['student_id' => 12, 'position_id' => 6, 'department_id' => 1, 'party_list_id' => 3, 'election_id' => 2],
            ['student_id' => 13, 'position_id' => 7, 'department_id' => 1, 'party_list_id' => 1, 'election_id' => 2],
            ['student_id' => 14, 'position_id' => 7, 'department_id' => 1, 'party_list_id' => 3, 'election_id' => 2],
            ['student_id' => 15, 'position_id' => 8, 'department_id' => 1, 'party_list_id' => 1, 'election_id' => 2],
            ['student_id' => 16, 'position_id' => 8, 'department_id' => 1, 'party_list_id' => 3, 'election_id' => 2],
            ['student_id' => 17, 'position_id' => 9, 'department_id' => 1, 'party_list_id' => 1, 'election_id' => 2],
            ['student_id' => 18, 'position_id' => 9, 'department_id' => 1, 'party_list_id' => 3, 'election_id' => 2],
            ['student_id' => 19, 'position_id' => 10, 'department_id' => 1, 'party_list_id' => 1, 'election_id' => 2],
            ['student_id' => 20, 'position_id' => 10, 'department_id' => 1, 'party_list_id' => 3, 'election_id' => 2],


            // CON Users (Only for Election 3)
            ['student_id' => 21, 'position_id' => 11, 'department_id' => 2, 'party_list_id' => 4, 'election_id' => 3],
            ['student_id' => 22, 'position_id' => 11, 'department_id' => 2, 'party_list_id' => 1, 'election_id' => 3],
            ['student_id' => 23, 'position_id' => 12, 'department_id' => 2, 'party_list_id' => 4, 'election_id' => 3],
            ['student_id' => 24, 'position_id' => 12, 'department_id' => 2, 'party_list_id' => 1, 'election_id' => 3],
            ['student_id' => 25, 'position_id' => 13, 'department_id' => 2, 'party_list_id' => 4, 'election_id' => 3],
            ['student_id' => 26, 'position_id' => 13, 'department_id' => 2, 'party_list_id' => 1, 'election_id' => 3],
            ['student_id' => 27, 'position_id' => 14, 'department_id' => 2, 'party_list_id' => 4, 'election_id' => 3],
            ['student_id' => 28, 'position_id' => 14, 'department_id' => 2, 'party_list_id' => 1, 'election_id' => 3],
            ['student_id' => 29, 'position_id' => 15, 'department_id' => 2, 'party_list_id' => 4, 'election_id' => 3],
            ['student_id' => 30, 'position_id' => 15, 'department_id' => 2, 'party_list_id' => 1, 'election_id' => 3],
        ];
    
        // Loop through the user data and insert into candidates table
        foreach ($users as $user) {
            DB::table('candidates')->insert([
                'student_id' => $user['student_id'],
                'user_id' => $user['student_id'], // Assuming user_id corresponds to student_id
                'election_id' => $user['election_id'],
                'department_id' => $user['department_id'],
                'position_id' => $user['position_id'],
                'party_list_id' => $user['party_list_id'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
    
}
