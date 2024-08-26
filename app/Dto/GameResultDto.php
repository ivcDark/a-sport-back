<?php

namespace App\Dto;

class GameResultDto
{
    public $gameId = null;
    public $homeGoals = null;
    public $guestGoals = null;

    public function __construct(array $data)
    {
        $this->gameId     = $data['gameId'] ?? null;
        $this->homeGoals  = $data['homeGoals'] ?? null;
        $this->guestGoals = $data['guestGoals'] ?? null;
    }
}
