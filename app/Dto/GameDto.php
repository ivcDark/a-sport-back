<?php

namespace App\Dto;

class GameDto
{
    public $clubHomeId = null;
    public $clubGuestId = null;
    public $leagueSeasonId = null;
    public $tour = null;
    public $timeStart = null;
    public $apiId = null;

    public function __construct(array $data)
    {
        $this->clubHomeId     = $data['clubHomeId'] ?? null;
        $this->clubGuestId    = $data['clubGuestId'] ?? null;
        $this->leagueSeasonId = $data['leagueSeasonId'] ?? null;
        $this->tour           = $data['tour'] ?? null;
        $this->timeStart      = $data['timeStart'] ?? null;
        $this->apiId          = $data['apiId'] ?? null;
    }
}
