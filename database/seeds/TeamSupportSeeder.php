<?php

use Illuminate\Database\Seeder;
use App\TeamSupport;

class TeamSupportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $teams = [
            ['name' => 'Team Network'],
            ['name' => 'Team Server'],
            ['name' => 'Team Security'],
            ['name' => 'Team Desktop'],
            ['name' => 'Team Application'],
            ['name' => 'Team Cloud'],
        ];

        foreach ($teams as $team) {
            TeamSupport::updateOrCreate(['name' => $team['name']], $team);
        }
    }
}
