<?php

namespace App\Dto;

class PlayerDto
{
    public $fio = null;
    public $number = null;
    public $clubId = null;
    public $countryId = null;
    public $slug = null;
    public $birthday = null;
    public $position = null;
    public $fieldName = null;
    public $apiName = null;
    public $apiId = null;
    public $logo = null;

    public function __construct(array $data)
    {
        $this->fio       = $data['fio'] ?? null;
        $this->number    = $data['number'] ?? null;
        $this->clubId    = $data['clubId'] ?? null;
        $this->countryId = $data['countryId'] ?? null;
        $this->slug      = $data['slug'] ?? null;
        $this->birthday  = $data['birthday'] ?? null;
        $this->position  = $data['position'] ?? null;
        $this->fieldName = $data['fieldName'] ?? null;
        $this->apiName   = $data['apiName'] ?? null;
        $this->apiId     = $data['apiId'] ?? null;
        $this->logo      = $data['logo'] ?? null;
    }
}
