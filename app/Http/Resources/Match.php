<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Match extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $homeTeam = new Team( $this->homeTeam );
        $awayTeam = new Team( $this->awayTeam );
        $league = [
          'id' => $this->league->id,
          'name' => $this->league->name
        ];

        $result = 'Not played';
        if ( $this->played == 1 ) {
            $result = $this->home_score . ' - ' . $this->away_score;
        }

        return [
            'league' => $league,
            'week' => $this->week,
            'home' => $homeTeam,
            'away' => $awayTeam,
            'result' => $result
        ];
    }
}
