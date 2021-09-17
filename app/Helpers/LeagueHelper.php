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

    /**
     * Get all leagues on system
     * @return League[]
     */
    public function getAllLeagues() {
        return League::all();
    }

    /**
     * Get last league on system
     * @return mixed
     */
    public function getLastLeague() {
        return League::find( \DB::table('leagues')->max('id' ) );
    }

    /**
     * Reset league schedule and matches
     * @param $leagueId
     * @return mixed
     */
    public function resetLeague( $leagueId ) {
        $league = League::find( $leagueId );
        $league->matches()->delete();
        $this->createLeagueMatches( $league );
        return $league;
    }

    /**
     * Create a new league
     * @param $leagueName
     * @return League
     */
    public function createNewLeague( $leagueName ) {
        $league = League::create([
            'name' => $leagueName,
        ]);
        $league = $league->fresh();

        $this->createLeagueMatches( $league );

        return $league;
    }

    /**
     * Get all league games
     * @param $leagueId
     * @return mixed
     */
    public function getLeagueGames( $leagueId ) {
        return League::find($leagueId)->matches;
    }

    /**
     * Get league games in certain week
     * @param $leagueId
     * @param $week
     * @return mixed
     */
    public function getWeekGames( $leagueId, $week ) {
        return Match::where('week', '=', $week)->where('league_id', '=', $leagueId)->get();
    }


    /**
     * Play all remaining games
     * @param $leagueId
     */
    public function playAllGames( $leagueId ) {
        $nextWeek = $this->getNextWeek( $leagueId );
        if ( !$nextWeek ) return;
        $this->playWeekGames($leagueId);
        $this->playAllGames($leagueId);
    }

    /**
     * Play week games
     * @param $leagueId
     */
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

    /**
     * Get current league standings
     * @param int $leagueId League id
     * @return array Current standings
     */
    public function getLeagueStandings( $leagueId ) {
        $teams = Team::all();
        $standings = [];
        foreach( $teams as $team ) {
            $result = $this->getTeamResultsFromMatches( $team, $leagueId );
            array_push( $standings, $result );
        }
        usort( $standings, function ( $res1, $res2 ) {
            if ( $res1["points"] > $res2["points"] ) return -1;
            else if ( $res1["points"] < $res2["points"] ) return 1;

            if ( $res1["gd"] > $res2["gd"] ) return -1;
            else if ( $res1["gd"] > $res2["gd"] ) return 1;

            return 0;
        } );
        return $standings;
    }


    // ===================================
    // Private helpers
    // ===================================

    /**
     * Get next week of league
     * @param $leagueId
     * @return string|null
     */
    private function getNextWeek( $leagueId ) {
        $nextMatch = Match::where('league_id', '=', $leagueId)->where('played', '=', '0')->get()->first();
        if ( !$nextMatch ) return null;
        return $nextMatch->week;
    }


    /**
     * Creates and attaches a schedule to the league
     * @param League $league
     * @param void
     */
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

    /**
     * Generates and returns the league schedule
     * @return array
     */
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

    /**
     * Get team results from the set of matches played
     * @param Team $team the team
     * @param int $leagueId League id
     * @return array
     */
    private function getTeamResultsFromMatches( $team, $leagueId ) {
        $matches = $team->matches();
        $matches = $matches->filter(function ($match) use ($leagueId) {
            return $match->league_id == $leagueId && $match->played == 1;
        })->values();
        $pts = 0;
        $wins = 0;
        $draws = 0;
        $loses = 0;
        $gd = 0;

        foreach( $matches as $match ) {
            if ( $match->homeTeam->id === $team->id ) {
                $matchDiff = $match->home_score - $match->away_score;
            } else {
                $matchDiff = $match->away_score - $match->home_score;
            }


            $gd += $matchDiff;
            if ( $matchDiff > 0 ) {
                $pts += 3;
                $wins++;
            } else if ( $matchDiff < 0 ) {
                $loses++;
            } else {
                $pts++;
                $draws++;
            }
        }

        return [
          'team' => $team->name,
          'points' => $pts,
          'wins' => $wins,
          'loses' => $loses,
          'gd' => $gd
        ];
    }


    /**
     * Given a set of teams generates a random league schedule
     * @param array $teams League teams
     * @return array
     */
    private function generateRandomDistribution( $teams ) {
        $teams = collect( $teams )->shuffle()->toArray();
        return $this->scheduler->BergerAlgorithm($teams);
    }

    /**
     * Gets a match score between two teams with possible surprises
     * @param Team $home Home team
     * @param Team $away Away team
     * @param bool $surprise if true a sudden change of events happens
     * @return array of the game score
     */
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

    // ===================================
    // Predictions
    // ===================================
    // Sketch book to get valid & dynamic predictions
    // 1. Recurse over upcoming matches with all possible scenarios and calculate for each team the odds to win the championship
    // 2. Dump approach ifs and else statically checking the odds (valid because of the "after 4th week" condition).
}
