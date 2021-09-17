<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class League extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
          'id' => $this->id,
          'name' => $this->name,
          'matches' => Match::collection( $this->matches )
        ];
    }
}
