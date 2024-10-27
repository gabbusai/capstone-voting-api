<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('students')->insert([
            [
                'id' => 1, 
                'year' => 3, 
                'name' => 'LeBron McPresident'
            ],

            [
                'id' => 2, 
                'year' => 3, 
                'name' => 'Donald Junk'
            ],

            [
                'id' => 3, 
                'year' => 3, 
                'name' => 'Joe Mama'
            ],

            [
                'id' => 4, 
                'year' => 3, 
                'name' => 'Kowala Ares'
            ],


            [
                'id' => 121300895, 
                'year' => 4, 
                'name' => 'John Gabriel Dayrit'
            ],
            [
                'id' => 123456789, 
                'year' => 4, 
                'name' => 'Admin'
            ],
            [
                'id' => 987654321, 
                'year' => 3, 
                'name' => 'Jane Doe'
            ],
            [
                'id' => 456123789, 
                'year' => 2, 
                'name' => 'Chris P. Bacon'
            ]
        ]);
    }
}
