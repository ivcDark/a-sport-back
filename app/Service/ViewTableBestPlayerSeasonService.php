<?php

namespace App\Service;

use App\Dto\ViewTableBestPlayerSeasonDto;
use App\Models\Country;
use App\Models\FilterTable;
use App\Models\ViewTableBestClubsSeason;
use App\Models\ViewTableBestPlayerSeason;
use Illuminate\Support\Facades\DB;

class ViewTableBestPlayerSeasonService
{
    private string $nameModel;

    public function __construct()
    {
        $this->nameModel = 'view_table_best_player_seasons';
    }

    public function create()
    {

    }

    public function updateOrCreate(ViewTableBestPlayerSeasonDto $viewTableBestPlayerSeasonDto): ?ViewTableBestPlayerSeason
    {
        $model = null;

        try {
            DB::beginTransaction();

            $model = ViewTableBestPlayerSeason::updateOrCreate(
                [
                    'club_id'          => $viewTableBestPlayerSeasonDto->clubId,
                    'season_id'        => $viewTableBestPlayerSeasonDto->seasonId,
                    'league_season_id' => $viewTableBestPlayerSeasonDto->leagueSeasonId,
                    'league_id'        => $viewTableBestPlayerSeasonDto->leagueId,
                    'section_game_id'  => $viewTableBestPlayerSeasonDto->sectionGameId,
                    'type_game_id'     => $viewTableBestPlayerSeasonDto->typeGameId,
                    'player_id'        => $viewTableBestPlayerSeasonDto->playerId,
                ],
                [
                    'club_id'          => $viewTableBestPlayerSeasonDto->clubId,
                    'season_id'        => $viewTableBestPlayerSeasonDto->seasonId,
                    'league_season_id' => $viewTableBestPlayerSeasonDto->leagueSeasonId,
                    'league_id'        => $viewTableBestPlayerSeasonDto->leagueId,
                    'type_game_id'     => $viewTableBestPlayerSeasonDto->typeGameId,
                    'section_game_id'  => $viewTableBestPlayerSeasonDto->sectionGameId,
                    'player_id'        => $viewTableBestPlayerSeasonDto->playerId,
                    'games_played'     => $viewTableBestPlayerSeasonDto->gamesPlayed,
                    'goals'            => $viewTableBestPlayerSeasonDto->goals,
                    'assist'           => $viewTableBestPlayerSeasonDto->assist,
                    'yellow_cards'     => $viewTableBestPlayerSeasonDto->yellowCards,
                    'red_cards'        => $viewTableBestPlayerSeasonDto->redCards,
                    'rating'           => $viewTableBestPlayerSeasonDto->rating,
                ]
            );

            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
            dd($exception->getMessage(), $exception->getTraceAsString());
        }

        return $model;

    }

    public function get(array $filters)
    {
        $result = ViewTableBestPlayerSeason::query();

        if (isset($filters['country_id'])) {
            $leagues = Country::with('leagues')
                ->whereIn('countries.id', $filters['country_id'])
                ->get()
                ->flatMap(fn($country) => $country->leagues->pluck('id'))
                ->toArray();
            $result = $result->whereIn('league_id', $leagues);
        } else {
            $leagues = Country::with('leagues')
                ->where('countries.name', 'Russia')
                ->get()
                ->flatMap(fn($country) => $country->leagues->pluck('id'))
                ->toArray();
            $result = $result->whereIn('league_id', $leagues);
        }

        if (isset($filters['league_id'])) {
            $result = $result->whereIn('league_id', $filters['league_id']);
        }

        if (isset($filters['season_id'])) {
            $result = $result->whereIn('season_id', $filters['season_id']);
        } else {
            $result = $result->where('season_id', 'cc211e20-81a4-4a29-824a-0d1b6958ae36'); //2024/2025
        }

        if (isset($filters['type_game_id'])) {
            $result = $result->where('type_game_id', $filters['type_game_id']);
        } else {
            $typeGame = FilterTable::homeGuestOnlyAll()->first();
            $result = $result->where('type_game_id', $typeGame->id);
        }

        $order = isset($filters['order']) ? FilterTable::find($filters['order'])->sub_name : 'goals';
        $limit = isset($filters['limit']) && $filters['limit'] > 0 ? $filters['limit'] : 10;

        return $result->orderByDesc($order)->limit($limit)->get();
    }
}
