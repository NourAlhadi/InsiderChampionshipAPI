<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Match extends Model
{

    protected $fillable = [ 'home_team_id', 'away_team_id', 'league_id', 'played', 'week' ];

    public function league() {
        return $this->belongsTo( League::class, 'league_id' );
    }

    public function homeTeam() {
        return $this->belongsTo( Team::class, 'home_team_id' );
    }

    public function awayTeam() {
        return $this->belongsTo( Team::class, 'away_team_id' );
    }

    public function scopeInLeague( $q, $leagueId ) {
        return $q->where('league_id', '=', $leagueId)->get();
    }

}
