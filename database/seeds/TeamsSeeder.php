<?php

use App\Models\Team;
use Illuminate\Database\Seeder;

class TeamsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        $teams = [
            [ 'name' => 'Arsenal', 'strength' => 0 ],
            [ 'name' => 'Chelsea', 'strength' => 1 ],
            [ 'name' => 'Liverpool', 'strength' => 2 ],
            [ 'name' => 'Man City', 'strength' => 3 ],
        ];

        foreach ( $teams as $team ) {
            Team::create( $team );
        }
    }
}
