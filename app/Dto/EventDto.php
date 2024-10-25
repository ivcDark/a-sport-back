<?php

namespace App\Dto;

class EventDto
{
    public $gameId = null;
    public $clubId = null;
    public $playerId = null;
    public $type = null;
    public $minute = null;
    public $sectionGame = null;
    public $value = null;

    public function __construct(array $data)
    {
        $this->gameId      = $data['gameId'] ?? null;
        $this->clubId      = $data['clubId'] ?? null;
        $this->playerId    = $data['playerId'] ?? null;
        $this->type        = $data['type'] ?? null;
        $this->minute      = $data['minute'] ?? null;
        $this->sectionGame = $data['sectionGame'] ?? null;
        $this->value       = $data['value'] ?? null;
    }
}
