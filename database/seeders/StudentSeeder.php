<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        // Students for SSC, CCIS, and CON
        $students = [
            //admin
            ['id' => '9999',  'name' => 'Admin', 'department_id' => 1],
            // SSC Students (General)
            ['id' => 122302639,  'name' => 'Rohit Dubb', 'department_id' => 6],
            ['id' => 122303562,  'name' => 'Ayron D. Cagara', 'department_id' => 6],

            //VPI
            ['id' => 124302188,  'name' => 'Jaswin Glent H. Pineda', 'department_id' => 5],
            ['id' => 121300291,  'name' => 'Zharren Manalastas', 'department_id' => 5],

            //VPE
            ['id' => 121300370,  'name' => 'Raiza C. Pineda', 'department_id' => 7],
            ['id' => 122303547,  'name' => 'Dexter N. Rivera', 'department_id' => 7],

            ['id' => 121300387,  'name' => 'Lineth T. Pamintuan', 'department_id' => 7],
            ['id' => 122301256,  'name' => 'Mina P. Mallari', 'department_id' => 3],

            //treasurer
            ['id' => 122300700,  'name' => 'Gemarshane Tirona', 'department_id' => 4],
            ['id' => 123301058,  'name' => 'James Labso', 'department_id' => 4],

            //PIO
            ['id' => 124300222,  'name' => 'Moriah Cassandra Mariano', 'department_id' => 2],
            ['id' => 124302025,  'name' => 'Russel Gegante', 'department_id' => 2],



            /*
            ['id' => 127509021,  'name' => 'Peter L. Cruz', 'department_id' => 1],
            ['id' => 127509022,  'name' => 'Alexander S. Pereira', 'department_id' => 2],
            ['id' => 127509023,  'name' => 'Jacob Marcus Elquiero', 'department_id' => 3],
            ['id' => 127509024,  'name' => 'Joseph A. Manalang', 'department_id' => 4],
            ['id' => 127509025,  'name' => 'Joanna Paula D. Santiago', 'department_id' => 5],
            ['id' => 127509026,  'name' => 'Kyle James A. Perez ', 'department_id' => 7],
            ['id' => 127509027,  'name' => 'David Jeffrey B. Punzalan', 'department_id' => 6],
            ['id' => 127509028,  'name' => 'Michael D. Canlas', 'department_id' => 3],
            ['id' => 127509029,  'name' => 'Kyla R. Agustin', 'department_id' => 4],

            
            ['id' => 127509030, 'name' => 'Marvin Dave Tinio', 'department_id' => 3],
            ['id' => 127509031,  'name' => 'Maria Leonora P. Dela Cruz', 'department_id' => 5],
            ['id' => 127509032,  'name' => 'Arnold S. Del Rosario', 'department_id' => 6],
            ['id' => 127509033,  'name' => 'Kimberly Cassandra L. Dizon', 'department_id' => 7],
            ['id' => 127509034,  'name' => 'Mark Lawrenz C. Tuazon', 'department_id' => 4],
            ['id' => 127509035, 'name' => 'Katherine S. Villaruel', 'department_id' => 2],
            */

            /*
            // CCIS Students (Department)
            ['id' => 11,  'name' => 'Joey Diaz', 'department_id' => 1],
            ['id' => 12,  'name' => 'Chris Bacon', 'department_id' => 1],
            ['id' => 13,  'name' => 'Jane Doe', 'department_id' => 1,],
            ['id' => 14,  'name' => 'Emma Watson', 'department_id' => 1],
            ['id' => 15,  'name' => 'Harry Potter', 'department_id' => 1],
            ['id' => 16,  'name' => 'Albus Dumbledore', 'department_id' => 1],
            ['id' => 17,  'name' => 'Hermione Granger', 'department_id' => 1],
            ['id' => 18,  'name' => 'Ron Weasley', 'department_id' => 1],
            ['id' => 19,  'name' => 'Gandalf', 'department_id' => 1],
            ['id' => 20,  'name' => 'Frodo Baggins', 'department_id' => 1],
            
            // CON Students (Department)
            ['id' => 21, 'name' => 'John Wick', 'department_id' => 2],
            ['id' => 22, 'name' => 'Arya Stark', 'department_id' => 2],
            ['id' => 23, 'name' => 'Daenerys Targaryen', 'department_id' => 2],
            ['id' => 24,  'name' => 'Sansa Stark', 'department_id' => 2],
            ['id' => 25,  'name' => 'Cersei Lannister', 'department_id' => 2],
            ['id' => 26,  'name' => 'Tyrion Lannister', 'department_id' => 2],
            ['id' => 27,  'name' => 'Jon Snow', 'department_id' => 2],
            ['id' => 28,  'name' => 'Jorah Mormont', 'department_id' => 2],
            ['id' => 29,  'name' => 'Theon Greyjoy', 'department_id' => 2],
            ['id' => 30,  'name' => 'Brienne of Tarth', 'department_id' => 2],
            */
            //TESTING BUKAS
            /*['id' => '0121300895',  'name' => 'John Gabriel Dayrit', 'department_id' => 1],
            ['id' => '0121302082',  'name' => 'John Aurvey Villapana', 'department_id' => 1],
            ['id' => '0121302060',  'name' => 'Ianne Lloyd Amerson C. Chua', 'department_id' => 1],
            ['id' => '0121300648',  'name' => 'Mark Daniel Torres', 'department_id' => 1],
            ['id' => '0122303517',  'name' => 'Jesus Brown', 'department_id' => 1],
            ['id' => '0120301756',  'name' => 'Nelson Intal', 'department_id' => 1], */
            
            //['id'=> '012345',  'name' => 'Test Smith', 'department_id' => 1],
            
        ];

        DB::table('students')->insert($students);
    }
}