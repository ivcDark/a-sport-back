<?php

namespace App\Service;

use App\Dto\ClubDto;
use App\Dto\CountryDto;
use App\Models\ClubLeague;
use App\Models\ModelIntegration;
use Illuminate\Support\Facades\DB;

class ClubService
{
    private string $typeIntegration;
    private string $nameModel;

    public function __construct($typeIntegration)
    {
        $this->typeIntegration = $typeIntegration;
        $this->nameModel = 'club';
    }

    public function create()
    {

    }

    public function updateOrCreate(ClubDto $clubDto): ?\App\Models\Club
    {
        $club = null;

        try {
            DB::beginTransaction();

            $club = \App\Models\Club::updateOrCreate(
                [
                    'name' => $clubDto->name,
                    'full_name' => $clubDto->fullName,
                    'code' => $clubDto->code,
                    'slug' => $clubDto->code,
                    'country_id' => $clubDto->countryId,
                ],
                [
                    'name' => $clubDto->name,
                    'full_name' => $clubDto->fullName,
                    'code' => $clubDto->code,
                    'slug' => $clubDto->code,
                    'country_id' => $clubDto->countryId,
                ]
            );

            ModelIntegration::firstOrCreate(
                [
                    'model_id' => $club->id,
                    'model' => $this->nameModel,
                    'type_integration' => $this->typeIntegration,
                    'integration_id' => $clubDto->apiId
                ],
                [
                    'model_id' => $club->id,
                    'model' => $this->nameModel,
                    'type_integration' => $this->typeIntegration,
                    'integration_id' => $clubDto->apiId
                ]
            );

            ClubLeague::firstOrCreate(
                [
                    'club_id' => $club->id,
                    'league_season_id' => $clubDto->leagueSeasonId
                ],
                [
                    'club_id' => $club->id,
                    'league_season_id' => $clubDto->leagueSeasonId
                ]
            );

            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
            dd($exception->getMessage());
        }

        return $club;

    }
}
