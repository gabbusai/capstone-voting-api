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
            ['id' => 1, 'year' => 3, 'name' => 'Jon Jones', 'department_id' => 1],
            ['id' => 2, 'year' => 3, 'name' => 'Alex Pereira', 'department_id' => 2],
            ['id' => 3, 'year' => 3, 'name' => 'Michael Bisping', 'department_id' => 3],
            ['id' => 4, 'year' => 3, 'name' => 'Joe Rogan', 'department_id' => 4],
            ['id' => 5, 'year' => 3, 'name' => 'Sarah Connor', 'department_id' => 5],
            ['id' => 6, 'year' => 3, 'name' => 'Bruce Wayne', 'department_id' => 1],
            ['id' => 7, 'year' => 3, 'name' => 'Clark Kent', 'department_id' => 2],
            ['id' => 8, 'year' => 3, 'name' => 'Tony Stark', 'department_id' => 3],
            ['id' => 9, 'year' => 3, 'name' => 'Peter Parker', 'department_id' => 4],
            ['id' => 10, 'year' => 3, 'name' => 'Natasha Romanoff', 'department_id' => 3],
            
            // CCIS Students (Department)
            ['id' => 11, 'year' => 3, 'name' => 'Joey Diaz', 'department_id' => 1],
            ['id' => 12, 'year' => 3, 'name' => 'Chris Bacon', 'department_id' => 1],
            ['id' => 13, 'year' => 3, 'name' => 'Jane Doe', 'department_id' => 1,],
            ['id' => 14, 'year' => 2, 'name' => 'Emma Watson', 'department_id' => 1],
            ['id' => 15, 'year' => 2, 'name' => 'Harry Potter', 'department_id' => 1],
            ['id' => 16, 'year' => 3, 'name' => 'Albus Dumbledore', 'department_id' => 1],
            ['id' => 17, 'year' => 3, 'name' => 'Hermione Granger', 'department_id' => 1],
            ['id' => 18, 'year' => 3, 'name' => 'Ron Weasley', 'department_id' => 1],
            ['id' => 19, 'year' => 2, 'name' => 'Gandalf', 'department_id' => 1],
            ['id' => 20, 'year' => 3, 'name' => 'Frodo Baggins', 'department_id' => 1],
            
            // CON Students (Department)
            ['id' => 21, 'year' => 3, 'name' => 'John Wick', 'department_id' => 2],
            ['id' => 22, 'year' => 3, 'name' => 'Arya Stark', 'department_id' => 2],
            ['id' => 23, 'year' => 2, 'name' => 'Daenerys Targaryen', 'department_id' => 2],
            ['id' => 24, 'year' => 3, 'name' => 'Sansa Stark', 'department_id' => 2],
            ['id' => 25, 'year' => 3, 'name' => 'Cersei Lannister', 'department_id' => 2],
            ['id' => 26, 'year' => 2, 'name' => 'Tyrion Lannister', 'department_id' => 2],
            ['id' => 27, 'year' => 2, 'name' => 'Jon Snow', 'department_id' => 2],
            ['id' => 28, 'year' => 3, 'name' => 'Jorah Mormont', 'department_id' => 2],
            ['id' => 29, 'year' => 3, 'name' => 'Theon Greyjoy', 'department_id' => 2],
            ['id' => 30, 'year' => 3, 'name' => 'Brienne of Tarth', 'department_id' => 2],

            //TESTING BUKAS
            ['id' => '0121300895', 'year' => 4, 'name' => 'John Gabriel Dayrit', 'department_id' => 1],
            ['id' => '0121302082', 'year' => 4, 'name' => 'John Aurvey Villapana', 'department_id' => 1],
            ['id' => '0121302060', 'year' => 4, 'name' => 'Ianne Lloyd Amerson C. Chua', 'department_id' => 1],
            ['id' => '0121300648', 'year' => 4, 'name' => 'Mark Daniel Torres', 'department_id' => 1],
            
            ['id'=> '012345', 'year' => 1, 'name' => 'Test Smith', 'department_id' => 1],
            ['id' => '9999', 'year' => 4, 'name' => 'Admin', 'department_id' => 1],
        ];

        DB::table('students')->insert($students);
    }
}