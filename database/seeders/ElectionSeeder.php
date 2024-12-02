<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class ElectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */

        public function run(): void
        {
            // Set the current time
            $now = Carbon::now();
            // Create a mock election
            DB::table('elections')->insert([
                [
                    'election_type_id' => 1, // general election
                    'department_id' => null, // Setting to null for general election
                    'election_name' => 'Supreme Student Council Elections 2024',
                    'campaign_start_date' => $now,
                    'campaign_end_date' => $now->copy()->addWeek(), // One week from now
                    'election_start_date' => $now->copy()->addWeek()->addDay(), // Starts after campaign ends
                    'election_end_date' => $now->copy()->addWeek()->addDays(3), // Ends two days after it starts
                    'status' => 'upcoming', // Initial status
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
    
                [
                    'election_type_id' => 2, // Department Election
                    'department_id' => 1, // 1 for CCIS
                    'election_name' => 'CCIS Student Council Elections 2024',
                    'campaign_start_date' => $now,
                    'campaign_end_date' => $now->copy()->addWeek(), // One week from now
                    'election_start_date' => $now->copy()->addWeek()->addDay(), // Starts after campaign ends
                    'election_end_date' => $now->copy()->addWeek()->addDays(3), // Ends two days after it starts
                    'status' => 'upcoming', // Initial status
                    'created_at' => now(),
                    'updated_at' => now(),
                ],

                [
                    'election_type_id' => 2, // Department Election
                    'department_id' => 2, // 1 for CCIS
                    'election_name' => 'CON Student Council Elections 2024',
                    'campaign_start_date' => $now,
                    'campaign_end_date' => $now->copy()->addWeek(), // One week from now
                    'election_start_date' => $now->copy()->addWeek()->addDay(), // Starts after campaign ends
                    'election_end_date' => $now->copy()->addWeek()->addDays(3), // Ends two days after it starts
                    'status' => 'upcoming', // Initial status
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
    
            ]);
        }
    }
