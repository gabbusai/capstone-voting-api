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
            ['id' => 1, 'year' => 3, 'name' => 'Jon Jones'],
            ['id' => 2, 'year' => 3, 'name' => 'Alex Pereira'],
            ['id' => 3, 'year' => 3, 'name' => 'Michael Bisping'],
            ['id' => 4, 'year' => 3, 'name' => 'Joe Rogan'],
            ['id' => 5, 'year' => 3, 'name' => 'Sarah Connor'],
            ['id' => 6, 'year' => 3, 'name' => 'Bruce Wayne'],
            ['id' => 7, 'year' => 3, 'name' => 'Clark Kent'],
            ['id' => 8, 'year' => 3, 'name' => 'Tony Stark'],
            ['id' => 9, 'year' => 3, 'name' => 'Peter Parker'],
            ['id' => 10, 'year' => 3, 'name' => 'Natasha Romanoff'],
            
            // CCIS Students (Department)
            ['id' => 11, 'year' => 3, 'name' => 'Joey Diaz'],
            ['id' => 12, 'year' => 3, 'name' => 'Chris Bacon'],
            ['id' => 13, 'year' => 3, 'name' => 'Jane Doe'],
            ['id' => 14, 'year' => 2, 'name' => 'Emma Watson'],
            ['id' => 15, 'year' => 2, 'name' => 'Harry Potter'],
            ['id' => 16, 'year' => 3, 'name' => 'Albus Dumbledore'],
            ['id' => 17, 'year' => 3, 'name' => 'Hermione Granger'],
            ['id' => 18, 'year' => 3, 'name' => 'Ron Weasley'],
            ['id' => 19, 'year' => 2, 'name' => 'Gandalf'],
            ['id' => 20, 'year' => 3, 'name' => 'Frodo Baggins'],
            
            // CON Students (Department)
            ['id' => 21, 'year' => 3, 'name' => 'John Wick'],
            ['id' => 22, 'year' => 3, 'name' => 'Arya Stark'],
            ['id' => 23, 'year' => 2, 'name' => 'Daenerys Targaryen'],
            ['id' => 24, 'year' => 3, 'name' => 'Sansa Stark'],
            ['id' => 25, 'year' => 3, 'name' => 'Cersei Lannister'],
            ['id' => 26, 'year' => 2, 'name' => 'Tyrion Lannister'],
            ['id' => 27, 'year' => 2, 'name' => 'Jon Snow'],
            ['id' => 28, 'year' => 3, 'name' => 'Jorah Mormont'],
            ['id' => 29, 'year' => 3, 'name' => 'Theon Greyjoy'],
            ['id' => 30, 'year' => 3, 'name' => 'Brienne of Tarth'],
        ];

        DB::table('students')->insert($students);
    }
}