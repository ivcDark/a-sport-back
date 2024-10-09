<?php

namespace App\Service;

use App\Dto\ClubDto;
use App\Dto\CountryDto;
use App\Models\Club;
use App\Models\ClubLeague;
use App\Models\LeagueSeason;
use App\Models\ModelIntegration;
use App\Models\Season;
use Illuminate\Support\Facades\DB;

class ClubService
{
    private ?string $typeIntegration;
    private string $nameModel;

    public function __construct($typeIntegration = null)
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

    public function get($params): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $clubs = Club::query();
        $season = isset($params['seasonId']) ? Season::find($params['seasonId']) : Season::current();

        if (isset($params['countryIds'])) {
            $ids = explode(',', $params['countryIds']);
            $clubs = $clubs->whereIn('country_id', $ids);
        }

        if (isset($params['leagueId'])) {
            $leagueSeason = LeagueSeason::where('league_id', $params['leagueId'])->where('season_id', $season->id)->first();
            $leagueSeasonClubsIds = ClubLeague::where('league_season_id', $leagueSeason->id)->pluck('club_id')->toArray();

            $clubs = $clubs->whereIn('id', $leagueSeasonClubsIds);
        }

        if (isset($params['clubIds'])) {
            $ids = explode(',', $params['clubIds']);
            $clubs = $clubs->whereIn('id', $ids);
        }

        if (isset($params['clubName'])) {
            $clubs = $clubs->where('name', 'like', "%{$params['clubName']}%");
        }

        $limit = isset($params['limit']) && $params['limit'] > 0 ? $params['limit'] : 20;

        $paginatedClubs = $clubs->paginate($limit);

        $paginatedClubs->getCollection()->transform(function ($club) use ($season) {
            $club->use_season_id = $season->id;
            return $club;
        });

        return $paginatedClubs;
    }
}
