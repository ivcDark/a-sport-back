<?php

namespace App\Dto;

class ViewTableBestClubSeasonDto
{
    public $clubId = null;
    public $seasonId = null;
    public $leagueId = null;
    public $leagueSeasonId = null;
    public $typeGameId = null;
    public $gamesPlayed = null;
    public $points = null;
    public $goalsScored = null;
    public $goalsConceded = null;
    public $goalsDiff = null;
    public $wins = null;
    public $yellowCards = null;
    public $redCards = null;
    public $avgBallPossession = null;

    public function __construct(array $data)
    {
        $this->clubId            = $data['clubId']            ?? null;
        $this->seasonId          = $data['seasonId']          ?? null;
        $this->leagueId          = $data['leagueId']          ?? null;
        $this->leagueSeasonId    = $data['leagueSeasonId']    ?? null;
        $this->typeGameId        = $data['typeGameId']        ?? null;
        $this->gamesPlayed       = $data['gamesPlayed']       ?? null;
        $this->points            = $data['points']            ?? null;
        $this->goalsScored       = $data['goalsScored']       ?? null;
        $this->goalsConceded     = $data['goalsConceded']     ?? null;
        $this->goalsDiff         = $data['goalsDiff']         ?? null;
        $this->wins              = $data['wins']              ?? null;
        $this->yellowCards       = $data['yellowCards']       ?? null;
        $this->redCards          = $data['redCards']          ?? null;
        $this->avgBallPossession = $data['avgBallPossession'] ?? null;
    }
}
