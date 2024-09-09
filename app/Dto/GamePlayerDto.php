<?php

namespace App\Dto;

class GamePlayerDto
{
    public $gameId = null;
    public $playerId = null;
    public $clubId = null;
    public $isStartGroup = null;
    public $isReserveGroup = null;
    public $isInjuredGroup = null;
    public $isBest = null;
    public $rating = null;
    public $apiId = null;

    public function __construct(array $data)
    {
        $this->gameId         = $data['gameId'] ?? null;
        $this->playerId       = $data['playerId'] ?? null;
        $this->clubId         = $data['clubId'] ?? null;
        $this->isStartGroup   = $data['isStartGroup'] ?? null;
        $this->isReserveGroup = $data['isReserveGroup'] ?? null;
        $this->isInjuredGroup = $data['isInjuredGroup'] ?? null;
        $this->isBest         = $data['isBest'] ?? null;
        $this->rating         = $data['rating'] ?? null;
        $this->apiId          = $data['apiId'] ?? null;
    }
}
