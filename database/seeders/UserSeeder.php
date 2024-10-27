<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        
        DB::table('users')->insert([
            //running for CCIS (dep id 1) position_id: 6
            [
                'name' => 'LeBron McPresident',
                'student_id' => 1,
                'department_id' => 1, // Assuming department CCIS
                'section' => '3A',
                'role_id' => 2, // Candidate
                'contact_no' => '09123456789',
                'email' => 'lebron@example.com',
                'email_verified_at' => now(),
                'password' => Hash::make('password123'),
                'remember_token' => Str::random(10),
                'created_at' => now(),
                'updated_at' => now(),
            ],

            //running for CCIS (dep id 1) position_id: 6
            [
                'name' => 'Donald Junk',
                'student_id' => 2,
                'department_id' => 2, // Assuming department CCIS
                'section' => '3B',
                'role_id' => 2, // Candidate
                'contact_no' => '09123456780',
                'email' => 'donald@example.com',
                'email_verified_at' => now(),
                'password' => Hash::make('password123'),
                'remember_token' => Str::random(10),
                'created_at' => now(),
                'updated_at' => now(),
            ],

            //running for CCIS (dep id 3) position_id: 1
            [
                'name' => 'Joe Mama',
                'student_id' => 3,
                'department_id' => 3, // WILL BE USED FOR SSC
                'section' => '3C',
                'role_id' => 2, // Candidate
                'contact_no' => '09123456781',
                'email' => 'joe@example.com',
                'email_verified_at' => now(),
                'password' => Hash::make('password123'),
                'remember_token' => Str::random(10),
                'created_at' => now(),
                'updated_at' => now(),
            ],

            //running for CCIS (dep id 4) position_id: 1
            [
                'name' => 'Kowala Ares',
                'student_id' => 4,
                'department_id' => 4, // WILL BE USED FOR SSC
                'section' => '3D',
                'role_id' => 2, // Candidate
                'contact_no' => '09123456782',
                'email' => 'kowala@example.com',
                'email_verified_at' => now(),
                'password' => Hash::make('password321'),
                'remember_token' => Str::random(10),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
