<?php

namespace App\Dto;

class GameStatisticDto
{
    public $gameId = null;
    public $clubId = null;
    public $sectionGame = null;
    public $typeIndicator = null;
    public $value = null;

    public function __construct(array $data)
    {
        $this->gameId        = $data['gameId'] ?? null;
        $this->clubId        = $data['clubId'] ?? null;
        $this->sectionGame   = $data['sectionGame'] ?? null;
        $this->typeIndicator = $data['typeIndicator'] ?? null;
        $this->value         = $data['value'] ?? null;
    }
}
