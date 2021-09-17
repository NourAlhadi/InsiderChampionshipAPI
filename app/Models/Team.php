<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Team extends Model
{

    protected $fillable = [ 'name', 'strength' ];
    protected $hidden = [ 'strength' ];

    public function homeMatches() {
        return $this->hasMany( Match::class, 'home_team_id' );
    }

    public function awayMatches() {
        return $this->hasMany( Match::class, 'away_team_id' );
    }

    public function matches() {
        return $this->homeMatches->merge($this->awayMatches);
    }


}
