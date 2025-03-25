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
            // SSC Students (General)
            ['id' => 1,  'name' => 'Jon Jones', 'department_id' => 1],
            ['id' => 2,  'name' => 'Alex Pereira', 'department_id' => 2],
            ['id' => 3,  'name' => 'Michael Bisping', 'department_id' => 3],
            ['id' => 4,  'name' => 'Joe Rogan', 'department_id' => 4],
            ['id' => 5,  'name' => 'Sarah Connor', 'department_id' => 5],
            ['id' => 6,  'name' => 'Bruce Wayne', 'department_id' => 1],
            ['id' => 7,  'name' => 'Clark Kent', 'department_id' => 2],
            ['id' => 8,  'name' => 'Tony Stark', 'department_id' => 3],
            ['id' => 9,  'name' => 'Peter Parker', 'department_id' => 4],
            ['id' => 10, 'name' => 'Natasha Romanoff', 'department_id' => 3],
            
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

            //TESTING BUKAS
            ['id' => '0121300895',  'name' => 'John Gabriel Dayrit', 'department_id' => 1],
            ['id' => '0121302082',  'name' => 'John Aurvey Villapana', 'department_id' => 1],
            ['id' => '0121302060',  'name' => 'Ianne Lloyd Amerson C. Chua', 'department_id' => 1],
            ['id' => '0121300648',  'name' => 'Mark Daniel Torres', 'department_id' => 1],
            ['id' => '0122303517',  'name' => 'Jesus Brown', 'department_id' => 1],
            ['id' => '0120301756',  'name' => 'Nelson Intal', 'department_id' => 1],
            
            ['id'=> '012345',  'name' => 'Test Smith', 'department_id' => 1],
            ['id' => '9999',  'name' => 'Admin', 'department_id' => 1],
        ];

        DB::table('students')->insert($students);
    }
}