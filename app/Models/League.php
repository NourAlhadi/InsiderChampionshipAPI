<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class League extends Model
{

    protected $fillable = [ 'name' ];

    public function matches() {
        return $this->hasMany( Match::class, 'league_id' );
    }

}
