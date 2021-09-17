<?php

namespace App\Helpers;


use App\Models\League;
use App\Models\Match;
use App\Models\Team;
use Gautile\RoundRobin\RoundRobinScheduler;

class LeagueHelper {

    protected $scheduler;

    public function __construct( RoundRobinScheduler $scheduler ) {
        $this->scheduler = $scheduler;
    }

    public function getLastLeague() {
        return League::find( \DB::table('leagues')->max('id' ) );
    }

    public function resetLeague( $leagueId ) {
        $league = League::find( $leagueId );
        $league->matches()->delete();
        $this->createLeagueMatches( $league );
        return $league;
    }

    public function createNewLeague( $leagueName ) {
        $league = League::create([
            'name' => $leagueName,
        ]);
        $league = $league->fresh();

        $this->createLeagueMatches( $league );

        return $league;
    }

    public function getLeagueGames( $leagueId ) {
        return League::find($leagueId)->matches;
    }

    public function getWeekGames( $leagueId, $week ) {
        return Match::where('week', '=', $week)->where('league_id', '=', $leagueId)->get();
    }

    public function getNextWeek( $leagueId ) {
        return Match::where('league_id', '=', $leagueId)->where('played', '=', '0')->get()->first()->week;
    }

    public function playWeekGames( $leagueId ) {
        $matches = $this->getWeekGames( $leagueId, $this->getNextWeek( $leagueId ) );
        foreach( $matches as $match ) {
            $home = $match->homeTeam;
            $away = $match->awayTeam;
            $score = $this->getScore( $home, $away );
            $match->played = 1;
            $match->home_score = $score[0];
            $match->away_score = $score[1];
            $match->save();
        }
    }

    public function getLeagueStandings( $leagueId ) {
        $teams = Team::all();
        foreach( $teams as $team ) {
            $matches = $team->matches();
            $matches = $matches->filter(function ($match) use ($leagueId) {
                return $match->league_id == $leagueId && $match->played == 1;
            })->values();
        }
        // TODO: complete this
    }


    // ===================================
    // Private helpers
    // ===================================
    private function generateLeagueSchedule() {
        $teams = Team::all()->pluck('id')->toArray();
        $distArray = $this->generateRandomDistribution( $teams );
        $halfSchedule = $distArray;
        $otherHalf = [];
        foreach( array_reverse( $distArray ) as $week ) {
            $newWeek = [];
            foreach( $week as $game ) array_push( $newWeek, array_reverse( $game ) );
            array_push( $otherHalf, $newWeek );
        }
        return array_merge( $halfSchedule, $otherHalf );
    }

    private function createLeagueMatches( $league, $schedule = null ) {
        if ( !$schedule ) $schedule = $this->generateLeagueSchedule();
        $weekId = 1;
        foreach ( $schedule as $week ) {
            foreach( $week as $game ) {
                Match::create([
                    'home_team_id' => $game[0],
                    'away_team_id' => $game[1],
                    'league_id' => $league->id,
                    'played' => 0,
                    'week' => $weekId
                ]);
            }
            $weekId++;
        }
    }


    private function generateRandomDistribution( $teams ) {
        $teams = collect( $teams )->shuffle()->toArray();
        return $this->scheduler->BergerAlgorithm($teams);
    }

    private function getScore( $home, $away, $surprise = false ) {
        $diff = $home->strength - $away->strength;
        if ( $surprise ) $diff += rand( 0, 2 );
        if ( $diff < 0 ) {
            return [ 0, -$diff ];
        } else if ( $diff > 0 ) {
            return [$diff, 0];
        }
        $tie = rand(0,3);
        return [ $tie, $tie ];
    }
}
