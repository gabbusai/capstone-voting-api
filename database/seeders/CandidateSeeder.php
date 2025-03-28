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
            // Position 1 (e.g., President)
            ['id' => 2,'student_id' => 127509021, 'position_id' => 1, 'department_id' => 1, 'party_list_id' => 1, 'election_id' => 1], // Peter L. Cruz
            ['id' => 3,'student_id' => 127509022, 'position_id' => 1, 'department_id' => 2, 'party_list_id' => 2, 'election_id' => 1], // Alexander S. Pereira
            ['id' => 4,'student_id' => 127509023, 'position_id' => 1, 'department_id' => 3, 'party_list_id' => 1, 'election_id' => 1], // Unknown Student
        
            // Position 2 (e.g., Vice President)
            ['id' => 5,'student_id' => 127509024, 'position_id' => 2, 'department_id' => 4, 'party_list_id' => 2, 'election_id' => 1], // Joseph A. Manalang
            ['id' => 6,'student_id' => 127509025, 'position_id' => 2, 'department_id' => 5, 'party_list_id' => 1, 'election_id' => 1], // Joanna Paula D. Santiago
            ['id' => 7,'student_id' => 127509026, 'position_id' => 2, 'department_id' => 1, 'party_list_id' => 2, 'election_id' => 1], // Kyle James A. Perez
        
            // Position 3 (e.g., Secretary)
            ['id' => 8,'student_id' => 127509027, 'position_id' => 3, 'department_id' => 6, 'party_list_id' => 1, 'election_id' => 1], // David Jeffrey B. Punzalan
            ['id' => 9,'student_id' => 127509028, 'position_id' => 3, 'department_id' => 3, 'party_list_id' => 2, 'election_id' => 1], // Michael D. Canlas
            ['id' => 10,'student_id' => 127509029, 'position_id' => 3, 'department_id' => 4, 'party_list_id' => 1, 'election_id' => 1], // Kyla R. Agustin
        
            // Position 4 (e.g., Treasurer)
            ['id' => 11,'student_id' => 127509030, 'position_id' => 4, 'department_id' => 3, 'party_list_id' => 2, 'election_id' => 1], // Marvin Dave Tinio
            ['id' => 12,'student_id' => 127509031, 'position_id' => 4, 'department_id' => 5, 'party_list_id' => 1, 'election_id' => 1], // Maria Leonora P. Dela Cruz
            ['id' => 13,'student_id' => 127509032, 'position_id' => 4, 'department_id' => 6, 'party_list_id' => 2, 'election_id' => 1], // Arnold S. Del Rosario
        
            // Position 5 (e.g., Auditor)
            ['id' => 14,'student_id' => 127509033, 'position_id' => 5, 'department_id' => 7, 'party_list_id' => 1, 'election_id' => 1], // Kimberly Cassandra L. Dizon
            ['id' => 15,'student_id' => 127509034, 'position_id' => 5, 'department_id' => 8, 'party_list_id' => 2, 'election_id' => 1], // Mark Lawrenz C. Tuazon
            ['id' => 16,'student_id' => 127509035, 'position_id' => 5, 'department_id' => 2, 'party_list_id' => 1, 'election_id' => 1], // Katherine S. Villaruel
        ];
    
        // Loop through the user data and insert into candidates table
        foreach ($users as $user) {
            DB::table('candidates')->insert([
                'student_id' => $user['student_id'],
                'user_id' => $user['id'], // Assuming user_id corresponds to student_id
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
