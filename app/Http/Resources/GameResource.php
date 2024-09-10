<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GameResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $homeGoals = $this->result == null ? '' : $this->result->home_goals;
        $guestGoals = $this->result == null ? '' : $this->result->guest_goals;
        $timeNow = time();
        $status = $this->result != null ? 'Finished' : ($timeNow > $this->time_start ? 'Live' : 'Not Started');

        return [
            'id'          => $this->id,
            'tour'        => $this->tour,
            'home_goals'  => $homeGoals,
            'guest_goals' => $guestGoals,
            'status'      => $status,
            'game_start'  => Carbon::createFromTimestamp($this->time_start)->addHour()->format('d.m.Y H:i'),
            'club_home'   => new ClubResource($this->clubHome),
            'club_guest'  => new ClubResource($this->clubGuest),
            'league'      => new LeagueResource($this->leagueSeason->league),
            'season'      => new SeasonResource($this->leagueSeason->season),
        ];
    }
}
