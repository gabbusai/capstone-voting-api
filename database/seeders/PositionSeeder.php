<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    
     public function run(): void
     {
         DB::table('positions')->insert([
             // General SSC Positions (no department ID needed)
             ['name' => 'SSC President', 'is_general' => true, 'department_id' => null],
             ['name' => 'SSC Vice President', 'is_general' => true, 'department_id' => null],
             ['name' => 'SSC Secretary', 'is_general' => true, 'department_id' => null],
             ['name' => 'SSC Treasurer', 'is_general' => true, 'department_id' => null],
             ['name' => 'SSC Auditor', 'is_general' => true, 'department_id' => null],
     
             // Department-Specific Positions for CCIS (department_id = 1)
             ['name' => 'CCIS President', 'is_general' => false, 'department_id' => 1],
             ['name' => 'CCIS Vice President', 'is_general' => false, 'department_id' => 1],
             ['name' => 'CCIS Secretary', 'is_general' => false, 'department_id' => 1],
             ['name' => 'CCIS Treasurer', 'is_general' => false, 'department_id' => 1],
             ['name' => 'CCIS Auditor', 'is_general' => false, 'department_id' => 1],
     
             // Department-Specific Positions for CON (department_id = 2)
             ['name' => 'CON President', 'is_general' => false, 'department_id' => 2],
             ['name' => 'CON Vice President', 'is_general' => false, 'department_id' => 2],
             ['name' => 'CON Secretary', 'is_general' => false, 'department_id' => 2],
             ['name' => 'CON Treasurer', 'is_general' => false, 'department_id' => 2],
             ['name' => 'CON Auditor', 'is_general' => false, 'department_id' => 2],
     
             // Department-Specific Positions for COB (department_id = 3)
             ['name' => 'COB President', 'is_general' => false, 'department_id' => 3],
             ['name' => 'COB Vice President', 'is_general' => false, 'department_id' => 3],
             ['name' => 'COB Secretary', 'is_general' => false, 'department_id' => 3],
             ['name' => 'COB Treasurer', 'is_general' => false, 'department_id' => 3],
             ['name' => 'COB Auditor', 'is_general' => false, 'department_id' => 3],
     
             // Department-Specific Positions for CHTM (department_id = 4)
             ['name' => 'CHTM President', 'is_general' => false, 'department_id' => 4],
             ['name' => 'CHTM Vice President', 'is_general' => false, 'department_id' => 4],
             ['name' => 'CHTM Secretary', 'is_general' => false, 'department_id' => 4],
             ['name' => 'CHTM Treasurer', 'is_general' => false, 'department_id' => 4],
             ['name' => 'CHTM Auditor', 'is_general' => false, 'department_id' => 4],
     
             // Department-Specific Positions for CCM (department_id = 5)
             ['name' => 'CCM President', 'is_general' => false, 'department_id' => 5],
             ['name' => 'CCM Vice President', 'is_general' => false, 'department_id' => 5],
             ['name' => 'CCM Secretary', 'is_general' => false, 'department_id' => 5],
             ['name' => 'CCM Treasurer', 'is_general' => false, 'department_id' => 5],
             ['name' => 'CCM Auditor', 'is_general' => false, 'department_id' => 5],
     
             // Department-Specific Positions for COE (department_id = 6)
             ['name' => 'COE President', 'is_general' => false, 'department_id' => 6],
             ['name' => 'COE Vice President', 'is_general' => false, 'department_id' => 6],
             ['name' => 'COE Secretary', 'is_general' => false, 'department_id' => 6],
             ['name' => 'COE Treasurer', 'is_general' => false, 'department_id' => 6],
             ['name' => 'COE Auditor', 'is_general' => false, 'department_id' => 6],

            // Department-Specific Positions for CASSED (department_id = 7)
             ['name' => 'CASSED President', 'is_general' => false, 'department_id' => 7],
             ['name' => 'CASSED Vice President', 'is_general' => false, 'department_id' => 7],
             ['name' => 'CASSED Secretary', 'is_general' => false, 'department_id' => 7],
             ['name' => 'CASSED Treasurer', 'is_general' => false, 'department_id' => 7],
             ['name' => 'CASSED Auditor', 'is_general' => false, 'department_id' => 7],


            // Department-Specific Positions for COC (department_id = 8)
            ['name' => 'COC President', 'is_general' => false, 'department_id' => 8],
            ['name' => 'COC Vice President', 'is_general' => false, 'department_id' => 8],
            ['name' => 'COC Secretary', 'is_general' => false, 'department_id' => 8],
            ['name' => 'COC Treasurer', 'is_general' => false, 'department_id' => 8],
            ['name' => 'COC Auditor', 'is_general' => false, 'department_id' => 8],
         ]);
     }
     


}
