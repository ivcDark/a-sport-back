<?php

namespace App\Dto;

class ClubDto
{
    public $name = null;
    public $fullName = null;
    public $code = null;
    public $countryId = null;
    public $leagueSeasonId = null;
    public $apiName = null;
    public $apiId = null;
    public $logo = null;

    public function __construct(array $data)
    {
        $this->name           = $data['name'] ?? null;
        $this->fullName       = $data['fullName'] ?? null;
        $this->code           = $data['code'] ?? null;
        $this->countryId      = $data['countryId'] ?? null;
        $this->leagueSeasonId = $data['leagueSeasonId'] ?? null;
        $this->apiName        = $data['apiName'] ?? null;
        $this->apiId          = $data['apiId'] ?? null;
        $this->logo           = $data['logo'] ?? null;
    }
}
