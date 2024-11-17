<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ViewTableGoalsPlayerToClubResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'player_name'         => $this->player_name,
            'player_image'        => $this->player_image,
            'club_name'           => $this->club_name,
            'player_goals'        => $this->player_goals,
            'team_goals'          => $this->team_goals,
            'goals_ratio_percent' => $this->goals_ratio_percent,
        ];
    }
}
