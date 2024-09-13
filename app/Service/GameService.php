<?php

namespace App\Service;

use App\Dto\GameDto;
use App\Models\Game;
use App\Models\League;
use App\Models\LeagueSeason;
use App\Models\ModelIntegration;
use App\Models\Season;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class GameService
{
    private ?string $typeIntegration;
    private string $nameModel;

    public function __construct($typeIntegration = null)
    {
        $this->typeIntegration = $typeIntegration;
        $this->nameModel = 'games';
    }

    public function create()
    {

    }

    public function updateOrCreate(GameDto $gameDto): ?\App\Models\Game
    {
        $game = null;

        try {
            DB::beginTransaction();

            $game = \App\Models\Game::updateOrCreate(
                [
                    'club_home_id'     => $gameDto->clubHomeId,
                    'club_guest_id'    => $gameDto->clubGuestId,
                    'league_season_id' => $gameDto->leagueSeasonId,
                    'tour'             => $gameDto->tour,
                    'time_start'       => $gameDto->timeStart,
                ],
                [
                    'club_home_id'     => $gameDto->clubHomeId,
                    'club_guest_id'    => $gameDto->clubGuestId,
                    'league_season_id' => $gameDto->leagueSeasonId,
                    'tour'             => $gameDto->tour,
                    'time_start'       => $gameDto->timeStart,
                ]
            );

            ModelIntegration::firstOrCreate(
                [
                    'model_id'         => $game->id,
                    'model'            => $this->nameModel,
                    'type_integration' => $this->typeIntegration,
                    'integration_id'   => $gameDto->apiId
                ],
                [
                    'model_id'         => $game->id,
                    'model'            => $this->nameModel,
                    'type_integration' => $this->typeIntegration,
                    'integration_id'   => $gameDto->apiId
                ]
            );

            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
            dd($exception->getMessage());
        }

        return $game;

    }

    public function get(array $filters)
    {
        $limit = isset($filters['limit']) && $filters['limit'] > 0 ? $filters['limit'] : 20;

        return Game::query()
            ->when(isset($filters['tour']), function ($query) use ($filters) {
                $query->where('tour', $filters['tour']);
            })
            ->when(isset($filters['date']), function ($query) use ($filters) {
                $startPeriod = Carbon::parse($filters['date'])->startOfDay()->timestamp;
                $endPeriod = Carbon::parse($filters['date'])->endOfDay()->timestamp;
                $query->whereBetween('time_start', [$startPeriod, $endPeriod]);
            })
            ->when(!isset($filters['date']), function ($query) use ($filters) {
                $startPeriod = Carbon::now()->startOfDay()->timestamp;
                $endPeriod = Carbon::now()->endOfDay()->timestamp;
                $query->whereBetween('time_start', [$startPeriod, $endPeriod]);
            })
            ->when(isset($filters['league_id']), function ($query) use ($filters) {
                $season = Season::where('id', $filters['season'] ?? 'cc211e20-81a4-4a29-824a-0d1b6958ae36')->first();
                $league = League::where('id', $filters['league_id'])->first();
                $leagueSeason = LeagueSeason::where('season_id', $season->id)->where('league_id', $league->id)->first();
                $query->where('league_season_id', $leagueSeason->id);
            })
            ->when(isset($filters['country_id']), function ($query) use ($filters) {
                $season = Season::where('id', $filters['season'] ?? 'cc211e20-81a4-4a29-824a-0d1b6958ae36')->first();
                $leagueIds = League::where('country_id', $filters['country_id'])->pluck('id')->toArray();
                $leagueSeasonIds = LeagueSeason::where('season_id', $season->id)->whereIn('league_id', $leagueIds)->pluck('id')->toArray();
                $query->whereIn('league_season_id', $leagueSeasonIds);
            })
            ->orderBy('time_start', 'desc')
            ->paginate($limit);
    }
}
