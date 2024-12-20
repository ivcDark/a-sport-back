<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClubResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'     => $this->id,
            'name'   => $this->name,
            'logo'   => $this->logo,
            'parent' => isset($this->use_season_id) ? LeagueResource::collection($this->leaguesInSeason($this->use_season_id)->get()->pluck('league')) : null
        ];
    }
}
