<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ViewTableBestPlayerSeasonResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'club'         => new ClubResource($this->club),
            'league'       => new LeagueResource($this->league),
            'type_game'    => new FilterTableResource($this->sectionGame),
            'player'       => new PlayerResource($this->player),
            'games_played' => $this->games_played,
            'goals'        => $this->goals,
            'assist'       => $this->assist,
            'yellow_cards' => $this->yellow_cards,
            'red_cards'    => $this->red_cards,
        ];
    }
}
