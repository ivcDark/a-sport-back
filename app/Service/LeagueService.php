<?php

namespace App\Service;

use App\Dto\CountryDto;
use App\Dto\LeagueDto;
use App\Models\LeagueSeason;
use App\Models\ModelIntegration;
use Illuminate\Support\Facades\DB;

class LeagueService
{
    private string $typeIntegration;
    private string $nameModel;

    public function __construct($typeIntegration)
    {
        $this->typeIntegration = $typeIntegration;
        $this->nameModel = 'league';
    }

    public function create()
    {

    }

    public function updateOrCreate(LeagueDto $leagueDto): ?\App\Models\League
    {
        $league = null;

        try {
            DB::beginTransaction();

            $league = \App\Models\League::updateOrCreate(
                [
                    'name'       => $leagueDto->name,
                    'code'       => $leagueDto->code,
                    'country_id' => $leagueDto->countryId,
                ],
                [
                    'name'       => $leagueDto->name,
                    'code'       => $leagueDto->code,
                    'country_id' => $leagueDto->countryId,
                    'logo'       => $leagueDto->logo,
                ]
            );

            $leagueSeason = LeagueSeason::updateOrCreate(
                [
                    'league_id' => $league->id,
                    'season_id' => 'cc211e20-81a4-4a29-824a-0d1b6958ae36'
                ],
                [
                    'league_id' => $league->id,
                    'season_id' => 'cc211e20-81a4-4a29-824a-0d1b6958ae36'
                ]
            );

            ModelIntegration::firstOrCreate(
                [
                    'model_id'         => $league->id,
                    'model'            => $this->nameModel,
                    'type_integration' => $this->typeIntegration,
                    'integration_id'   => $leagueDto->apiId
                ],
                [
                    'model_id'         => $league->id,
                    'model'            => $this->nameModel,
                    'type_integration' => $this->typeIntegration,
                    'integration_id'   => $leagueDto->apiId
                ]
            );

            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
            dd($exception->getMessage());
        }

        return $league;

    }
}