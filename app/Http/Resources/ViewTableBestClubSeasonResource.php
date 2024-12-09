<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ViewTableBestClubSeasonResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'club'                => new ClubResource($this->club),
            'league'              => new LeagueResource($this->league),
            'type_game'           => new FilterTableResource($this->typeGame),
            'games_played'        => $this->games_played,
            'points'              => $this->points,
            'goals_scored'        => $this->goals_scored,
            'goals_conceded'      => $this->goals_conceded,
            'goals_diff'          => $this->goals_diff,
            'wins'                => $this->wins,
            'yellow_cards'        => $this->yellow_cards,
            'red_cards'           => $this->red_cards,
            'avg_ball_possession' => $this->avg_ball_possession
        ];
    }
}
