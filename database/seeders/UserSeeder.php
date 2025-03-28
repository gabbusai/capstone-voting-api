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
            //admin
            ['id' => 1, 'name' => 'Admin', 'student_id' => '9999','department_id' => 1, 'role_id' => 3, 'contact_no' => '09123456789', 'email' => 'admin@example.com', 'section' => '5A', 'device_id' => 'device_0' ],
            // SSC Users (General)
            ['id' => 2,'name' => 'Rohit Dubb', 'student_id' => 122302639, 'department_id' => 6, 'role_id' => 2, 'contact_no' => '09123456701', 'email' => 'samplemail@example.com', 'section' => '4A', 'device_id' => 'device_01'],
            ['id' => 3,'name' => 'Ayron D. Cagara', 'student_id' => 122303562, 'department_id' => 6, 'role_id' => 2, 'contact_no' => '09123456702', 'email' => 'contactmail@example.com', 'section' => '4B', 'device_id' => 'device_02'],

            ['id' => 4,'name' => 'Jaswin Glent H. Pineda', 'student_id' => 124302188, 'department_id' => 5, 'role_id' => 2, 'contact_no' => '09123456701', 'email' => 'name.email@example.com', 'section' => '4A', 'device_id' => 'device_01'],
            ['id' => 5,'name' => 'Zharren Manalastas', 'student_id' => 121300291, 'department_id' => 5, 'role_id' => 2, 'contact_no' => '09123456702', 'email' => 'testMail123@example.com', 'section' => '4B', 'device_id' => 'device_02'],

            ['id' => 6,'name' => 'Raiza C. Pineda', 'student_id' => 121300370, 'department_id' => 7, 'role_id' => 2, 'contact_no' => '09123456701', 'email' => 'johndoe@example.com', 'section' => '4A', 'device_id' => 'device_01'],
            ['id' => 7,'name' => 'Dexter N. Rivera', 'student_id' => 122303547, 'department_id' => 7, 'role_id' => 2, 'contact_no' => '09123456702', 'email' => 'johndoe2@example.com', 'section' => '4B', 'device_id' => 'device_02'],

            ['id' => 8,'name' => 'Lineth T. Pamintuan', 'student_id' => 121300387, 'department_id' => 7, 'role_id' => 2, 'contact_no' => '09123456701', 'email' => 'johndoe3@example.com', 'section' => '4A', 'device_id' => 'device_01'],
            ['id' => 9,'name' => 'Mina P. Mallari', 'student_id' => 122301256, 'department_id' => 3, 'role_id' => 2, 'contact_no' => '09123456702', 'email' => 'johndoe4@example.com', 'section' => '4B', 'device_id' => 'device_02'],

            ['id' => 10,'name' => 'Gemarshane Tirona', 'student_id' => 122300700, 'department_id' => 4, 'role_id' => 2, 'contact_no' => '09123456701', 'email' => 'johndoe5@example.com', 'section' => '4A', 'device_id' => 'device_01'],
            ['id' => 11,'name' => 'James Labso', 'student_id' => 123301058, 'department_id' => 4, 'role_id' => 2, 'contact_no' => '09123456702', 'email' => 'johndoe6@example.com', 'section' => '4B', 'device_id' => 'device_02'],

            ['id' => 12,'name' => 'Moriah Cassandra Mariano', 'student_id' => 124300222, 'department_id' => 2, 'role_id' => 2, 'contact_no' => '09123456701', 'email' => 'elections@example.com', 'section' => '4A', 'device_id' => 'device_01'],
            ['id' => 13,'name' => 'Russel Gegante', 'student_id' => 124302025, 'department_id' => 2, 'role_id' => 2, 'contact_no' => '09123456702', 'email' => 'johndoe7@example.com', 'section' => '4B', 'device_id' => 'device_02'],

            
            /*
            ['id' => 2,'name' => 'Peter L. Cruz', 'student_id' => 127509021, 'department_id' => 1, 'role_id' => 2, 'contact_no' => '09123456701', 'email' => 'peter.cruz@example.com', 'section' => '4A', 'device_id' => 'device_01'],
            ['id' => 3,'name' => 'Alexander S. Pereira', 'student_id' => 127509022, 'department_id' => 2, 'role_id' => 2, 'contact_no' => '09123456702', 'email' => 'alex.pereira@example.com', 'section' => '4B', 'device_id' => 'device_02'],
            ['id' => 4,'name' => 'Jacob Marcus Elquiero', 'student_id' => 127509023, 'department_id' => 3, 'role_id' => 2, 'contact_no' => '09123456703', 'email' => 'elq@example.com', 'section' => '4C', 'device_id' => 'device_03'], // Empty name in your seeder
            ['id' => 5,'name' => 'Joseph A. Manalang', 'student_id' => 127509024, 'department_id' => 4, 'role_id' => 2, 'contact_no' => '09123456704', 'email' => 'joseph.manalang@example.com', 'section' => '4D', 'device_id' => 'device_04'],
            ['id' => 6,'name' => 'Joanna Paula D. Santiago', 'student_id' => 127509025, 'department_id' => 5, 'role_id' => 2, 'contact_no' => '09123456705', 'email' => 'joanna.santiago@example.com', 'section' => '5A', 'device_id' => 'device_05'],
            ['id' => 7,'name' => 'Kyle James A. Perez', 'student_id' => 127509026, 'department_id' => 1, 'role_id' => 2, 'contact_no' => '09123456706', 'email' => 'kyle.perez@example.com', 'section' => '5B', 'device_id' => 'device_06'],
            ['id' => 8,'name' => 'David Jeffrey B. Punzalan', 'student_id' => 127509027, 'department_id' => 6, 'role_id' => 2, 'contact_no' => '09123456707', 'email' => 'david.punzalan@example.com', 'section' => '5C', 'device_id' => 'device_07'],
            ['id' => 9,'name' => 'Michael D. Canlas', 'student_id' => 127509028, 'department_id' => 3, 'role_id' => 2, 'contact_no' => '09123456708', 'email' => 'michael.canlas@example.com', 'section' => '5D', 'device_id' => 'device_08'],
            ['id' => 10,'name' => 'Kyla R. Agustin', 'student_id' => 127509029, 'department_id' => 4, 'role_id' => 2, 'contact_no' => '09123456709', 'email' => 'kyla.agustin@example.com', 'section' => '4A', 'device_id' => 'device_09'],
            
            
            ['id' => 11,'name' => 'Marvin Dave Tinio', 'student_id' => 127509030, 'department_id' => 3, 'role_id' => 2, 'contact_no' => '09123456710', 'email' => 'marvin.tinio@example.com', 'section' => '4B', 'device_id' => 'device_10'],
            ['id' => 12,'name' => 'Maria Leonora P. Dela Cruz', 'student_id' => 127509031, 'department_id' => 5, 'role_id' => 2, 'contact_no' => '09123456711', 'email' => 'maria.delacruz@example.com', 'section' => '4C', 'device_id' => 'device_11'],
            ['id' => 13,'name' => 'Arnold S. Del Rosario', 'student_id' => 127509032, 'department_id' => 6, 'role_id' => 2, 'contact_no' => '09123456712', 'email' => 'arnold.delrosario@example.com', 'section' => '4D', 'device_id' => 'device_12'],
            ['id' => 14,'name' => 'Kimberly Cassandra L. Dizon', 'student_id' => 127509033, 'department_id' => 7, 'role_id' => 2, 'contact_no' => '09123456713', 'email' => 'kimberly.dizon@example.com', 'section' => '5A', 'device_id' => 'device_13'],
            ['id' => 15,'name' => 'Mark Lawrenz C. Tuazon', 'student_id' => 127509034, 'department_id' => 4, 'role_id' => 2, 'contact_no' => '09123456714', 'email' => 'mark.tuazon@example.com', 'section' => '5B', 'device_id' => 'device_14'],
            ['id' => 16,'name' => 'Katherine S. Villaruel', 'student_id' => 127509035, 'department_id' => 2, 'role_id' => 2, 'contact_no' => '09123456715', 'email' => 'katherine.villaruel@example.com', 'section' => '5C', 'device_id' => 'device_15'],
            */
            
            /*
            // CCIS Users
            ['name' => 'Joey Diaz', 'student_id' => 11, 'department_id' => 1, 'role_id' => 2, 'contact_no' => '09123456784', 'email' => 'diaz@example.com', 'section' => '4A', 'device_id' => 'device_01'],
            ['name' => 'Chris P. Bacon', 'student_id' => 12, 'department_id' => 1, 'role_id' => 2, 'contact_no' => '09123456785', 'email' => 'chris@example.com', 'section' => '4B', 'device_id' => 'device_02'],
            ['name' => 'Jane Doe', 'student_id' => 13, 'department_id' => 1, 'role_id' => 2, 'contact_no' => '09123456786', 'email' => 'jane@example.com',  'section' => '4C', 'device_id' => 'device_03'],
            ['name' => 'Emma Watson', 'student_id' => 14, 'department_id' => 1, 'role_id' => 2, 'contact_no' => '09123456787', 'email' => 'emma@example.com', 'section' => '4D', 'device_id' => 'device_20'],
            ['name' => 'Harry Potter', 'student_id' => 15, 'department_id' => 1, 'role_id' => 2, 'contact_no' => '09123456788', 'email' => 'harry@example.com', 'section' => '5C', 'device_id' => 'device_04'],
            ['name' => 'Albus Dumbledore', 'student_id' => 16, 'department_id' => 1, 'role_id' => 2, 'contact_no' => '09123456788', 'email' => 'dumbledore@example.com', 'section' => '5A', 'device_id' => 'device_05'],
            ['name' => 'Hermione Granger', 'student_id' => 17, 'department_id' => 1, 'role_id' => 2, 'contact_no' => '09123456788', 'email' => 'hermione@example.com', 'section' => '3B', 'device_id' => 'device_06'],
            ['name' => 'Ron Weasley', 'student_id' => 18, 'department_id' => 1, 'role_id' => 2, 'contact_no' => '09123456788', 'email' => 'ron@example.com', 'section' => '3A', 'device_id' => 'device_07'],
            ['name' => 'Gandalf', 'student_id' => 19, 'department_id' => 1, 'role_id' => 2, 'contact_no' => '09123456788', 'email' => 'gandalf@example.com', 'section' => '4C', 'device_id' => 'device_08'],
            ['name' => 'Frodo Baggins', 'student_id' => 20, 'department_id' => 1, 'role_id' => 2, 'contact_no' => '09123456788', 'email' => 'frodo@example.com', 'section' => '5D', 'device_id' => 'device_09'],

            // CON Users
            ['name' => 'John Wick', 'student_id' => 21, 'department_id' => 2, 'role_id' => 2, 'contact_no' => '09123456789', 'email' => 'johnwick@example.com', 'section' => '5A', 'device_id' => 'device_10'],
            ['name' => 'Arya Stark', 'student_id' => 22, 'department_id' => 2, 'role_id' => 2, 'contact_no' => '09123456790', 'email' => 'arya@example.com', 'section' => '4D', 'device_id' => 'device_11'],
            ['name' => 'Daenerys Targaryen', 'student_id' => 23, 'department_id' => 2, 'role_id' => 2, 'contact_no' => '09123456791', 'email' => 'daenerys@example.com', 'section' => '3A', 'device_id' => 'device_12'],
            ['name' => 'Sansa Stark', 'student_id' => 24, 'department_id' => 2, 'role_id' => 2, 'contact_no' => '09123456792', 'email' => 'sansa@example.com', 'section' => '3B', 'device_id' => 'device_13'],
            ['name' => 'Cersei Lannister', 'student_id' => 25, 'department_id' => 2, 'role_id' => 2, 'contact_no' => '09123456793', 'email' => 'cersei@example.com', 'section' => '4B', 'device_id' => 'device_14'],
            ['name' => 'Tyrion Lannister', 'student_id' => 26, 'department_id' => 2, 'role_id' => 2, 'contact_no' => '09123456793', 'email' => 'tyrion@example.com', 'section' => '4C', 'device_id' => 'device_15'],
            ['name' => 'Jon Snow', 'student_id' => 27, 'department_id' => 2, 'role_id' => 2, 'contact_no' => '09123456793', 'email' => 'jon@example.com', 'section' => '4C', 'device_id' => 'device_16'],
            ['name' => 'Jorah Mormont', 'student_id' => 28, 'department_id' => 2, 'role_id' => 2, 'contact_no' => '09123456793', 'email' => 'jorah@example.com', 'section' => '5A', 'device_id' => 'device_17'],
            ['name' => 'Theon Greyjoy', 'student_id' => 29, 'department_id' => 2, 'role_id' => 2, 'contact_no' => '09123456793', 'email' => 'theon@example.com', 'section' => '5B', 'device_id' => 'device_18'],
            ['name' => 'Brienne of Tarth', 'student_id' => 30, 'department_id' => 2, 'role_id' => 2, 'contact_no' => '09123456793', 'email' => 'tarth@example.com', 'section' => '4A', 'device_id' => 'device_19'],
            */

           
        ];

        foreach ($users as $user) {
            DB::table('users')->insert([
                'id' => $user['id'],
                'name' => $user['name'],
                'student_id' => $user['student_id'],
                'department_id' => $user['department_id'],
                'role_id' => $user['role_id'],
                //'contact_no' => $user['contact_no'],
                'email' => $user['email'],
                //'section' => $user['section'],
                'device_id' => $user['device_id'],
                'email_verified_at' => now(),
                //'password' => Hash::make('password123'),
                'remember_token' => Str::random(10),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}