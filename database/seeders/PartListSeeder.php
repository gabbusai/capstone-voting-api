<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class PartListSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('party_lists')->insert([
            ['name' => 'Independent', 'description' => 'Independent Candidates'],
            ['name' => 'Team A PartyList', 'description' => 'Random Description for partylist a'],
            ['name' => 'B Team', 'description' => 'Second Best PARTYLIST'],
            ['name' => 'Komy Party', 'description' => 'WE WILL WIN!'],
        ]);
    }
}
