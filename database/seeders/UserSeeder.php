<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            // SSC Users (General)
            ['name' => 'Jon Jones', 'student_id' => 1, 'department_id' => 1, 'role_id' => 2, 'contact_no' => '09123456789', 'email' => 'jon@example.com', 'section' => '4A'],
            ['name' => 'Alex Pereira', 'student_id' => 2, 'department_id' => 2, 'role_id' => 2, 'contact_no' => '09123456780', 'email' => 'alex@example.com', 'section' => '4B'],
            ['name' => 'Michael Bisping', 'student_id' => 3, 'department_id' => 3, 'role_id' => 2, 'contact_no' => '09123456781', 'email' => 'bisping@example.com', 'section' => '5A'],
            ['name' => 'Joe Rogan', 'student_id' => 4, 'department_id' => 4, 'role_id' => 2, 'contact_no' => '09123456782', 'email' => 'rogan@example.com', 'section' => '4C'],
            ['name' => 'Sarah Connor', 'student_id' => 5, 'department_id' => 5, 'role_id' => 2, 'contact_no' => '09123456783', 'email' => 'sarah@example.com', 'section' => '5B'],
            ['name' => 'Bruce Wayne', 'student_id' => 6, 'department_id' => 1, 'role_id' => 2, 'contact_no' => '09123456783', 'email' => 'batman@example.com', 'section' => '5C'],
            ['name' => 'Clark Kent', 'student_id' => 7, 'department_id' => 2, 'role_id' => 2, 'contact_no' => '09123456783', 'email' => 'superman@example.com', 'section' => '5D'],
            ['name' => 'Tony Stark', 'student_id' => 8, 'department_id' => 3, 'role_id' => 2, 'contact_no' => '09123456783', 'email' => 'ironman@example.com', 'section' => '5B'],
            ['name' => 'Peter Parker', 'student_id' => 9, 'department_id' => 4, 'role_id' => 2, 'contact_no' => '09123456783', 'email' => 'spiderman@example.com', 'section' => '4A'],
            ['name' => 'Natasha Romanoff', 'student_id' => 10, 'department_id' => 3, 'role_id' => 2, 'contact_no' => '09123456783', 'email' => 'blackwidow@example.com', 'section' => '4D'],

            
            
            // CCIS Users
            ['name' => 'Joey Diaz', 'student_id' => 11, 'department_id' => 1, 'role_id' => 2, 'contact_no' => '09123456784', 'email' => 'diaz@example.com', 'section' => '4A'],
            ['name' => 'Chris P. Bacon', 'student_id' => 12, 'department_id' => 1, 'role_id' => 2, 'contact_no' => '09123456785', 'email' => 'chris@example.com', 'section' => '4B'],
            ['name' => 'Jane Doe', 'student_id' => 13, 'department_id' => 1, 'role_id' => 2, 'contact_no' => '09123456786', 'email' => 'jane@example.com',  'section' => '4C'],
            ['name' => 'Emma Watson', 'student_id' => 14, 'department_id' => 1, 'role_id' => 2, 'contact_no' => '09123456787', 'email' => 'emma@example.com', 'section' => '4D'],
            ['name' => 'Harry Potter', 'student_id' => 15, 'department_id' => 1, 'role_id' => 2, 'contact_no' => '09123456788', 'email' => 'harry@example.com', 'section' => '5C'],
            ['name' => 'Albus Dumbledore', 'student_id' => 16, 'department_id' => 1, 'role_id' => 2, 'contact_no' => '09123456788', 'email' => 'dumbledore@example.com', 'section' => '5A'],
            ['name' => 'Hermione Granger', 'student_id' => 17, 'department_id' => 1, 'role_id' => 2, 'contact_no' => '09123456788', 'email' => 'hermione@example.com', 'section' => '3B'],
            ['name' => 'Ron Weasley', 'student_id' => 18, 'department_id' => 1, 'role_id' => 2, 'contact_no' => '09123456788', 'email' => 'ron@example.com', 'section' => '3A'],
            ['name' => 'Gandalf', 'student_id' => 19, 'department_id' => 1, 'role_id' => 2, 'contact_no' => '09123456788', 'email' => 'gandalf@example.com', 'section' => '4C'],
            ['name' => 'Frodo Baggins', 'student_id' => 20, 'department_id' => 1, 'role_id' => 2, 'contact_no' => '09123456788', 'email' => 'frodo@example.com', 'section' => '5D'],

            // CON Users
            ['name' => 'John Wick', 'student_id' => 21, 'department_id' => 2, 'role_id' => 2, 'contact_no' => '09123456789', 'email' => 'johnwick@example.com', 'section' => '5A'],
            ['name' => 'Arya Stark', 'student_id' => 22, 'department_id' => 2, 'role_id' => 2, 'contact_no' => '09123456790', 'email' => 'arya@example.com', 'section' => '4D'],
            ['name' => 'Daenerys Targaryen', 'student_id' => 23, 'department_id' => 2, 'role_id' => 2, 'contact_no' => '09123456791', 'email' => 'daenerys@example.com', 'section' => '3A'],
            ['name' => 'Sansa Stark', 'student_id' => 24, 'department_id' => 2, 'role_id' => 2, 'contact_no' => '09123456792', 'email' => 'sansa@example.com', 'section' => '3B'],
            ['name' => 'Cersei Lannister', 'student_id' => 25, 'department_id' => 2, 'role_id' => 2, 'contact_no' => '09123456793', 'email' => 'cersei@example.com', 'section' => '4B'],
            ['name' => 'Tyrion Lannister', 'student_id' => 26, 'department_id' => 2, 'role_id' => 2, 'contact_no' => '09123456793', 'email' => 'tyrion@example.com', 'section' => '4C'],
            ['name' => 'Jon Snow', 'student_id' => 27, 'department_id' => 2, 'role_id' => 2, 'contact_no' => '09123456793', 'email' => 'jon@example.com', 'section' => '4C'],
            ['name' => 'Jorah Mormont', 'student_id' => 28, 'department_id' => 2, 'role_id' => 2, 'contact_no' => '09123456793', 'email' => 'jorah@example.com', 'section' => '5A'],
            ['name' => 'Theon Greyjoy', 'student_id' => 29, 'department_id' => 2, 'role_id' => 2, 'contact_no' => '09123456793', 'email' => 'theon@example.com', 'section' => '5B'],
            ['name' => 'Brienne of Tarth', 'student_id' => 30, 'department_id' => 2, 'role_id' => 2, 'contact_no' => '09123456793', 'email' => 'tarth@example.com', 'section' => '4A'],

        ];

        foreach ($users as $user) {
            DB::table('users')->insert([
                'name' => $user['name'],
                'student_id' => $user['student_id'],
                'department_id' => $user['department_id'],
                'role_id' => $user['role_id'],
                'contact_no' => $user['contact_no'],
                'email' => $user['email'],
                'section' => $user['section'],
                'email_verified_at' => now(),
                'password' => Hash::make('password123'),
                'remember_token' => Str::random(10),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}