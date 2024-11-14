<?php

namespace App\Dto;

class ViewTableBestPlayerSeasonDto
{
    public $clubId = null;
    public $seasonId = null;
    public $leagueId = null;
    public $leagueSeasonId = null;
    public $sectionGameId = null;
    public $typeGameId = null;
    public $playerId = null;
    public $gamesPlayed = null;
    public $goals = null;
    public $assist = null;
    public $yellowCards = null;
    public $redCards = null;
    public $rating = null;

    public function __construct(array $data)
    {
        $this->clubId         = $data['clubId']         ?? null;
        $this->seasonId       = $data['seasonId']       ?? null;
        $this->leagueId       = $data['leagueId']       ?? null;
        $this->leagueSeasonId = $data['leagueSeasonId'] ?? null;
        $this->sectionGameId  = $data['sectionGameId']  ?? null;
        $this->typeGameId     = $data['typeGameId']     ?? null;
        $this->playerId       = $data['playerId']       ?? null;
        $this->gamesPlayed    = $data['gamesPlayed']    ?? null;
        $this->goals          = $data['goals']          ?? null;
        $this->assist         = $data['assist']         ?? null;
        $this->yellowCards    = $data['yellowCards']    ?? null;
        $this->redCards       = $data['redCards']       ?? null;
        $this->rating         = $data['rating']         ?? null;
    }
}
