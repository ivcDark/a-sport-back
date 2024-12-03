<?php

namespace App\Service;

use App\Dto\ViewTableBestClubSeasonDto;
use App\Models\Country;
use App\Models\FilterTable;
use App\Models\ViewTableBestClubsSeason;
use Illuminate\Support\Facades\DB;

class ViewTableBestClubSeasonService
{
    private string $nameModel;

    public function __construct()
    {
        $this->nameModel = 'view_table_best_clubs_seasons';
    }

    public function create()
    {

    }

    public function updateOrCreate(ViewTableBestClubSeasonDto $viewTableBestClubSeasonDto): ?ViewTableBestClubsSeason
    {
        $model = null;

        try {
            DB::beginTransaction();

            $model = ViewTableBestClubsSeason::updateOrCreate(
                [
                    'club_id'          => $viewTableBestClubSeasonDto->clubId,
                    'season_id'        => $viewTableBestClubSeasonDto->seasonId,
                    'league_season_id' => $viewTableBestClubSeasonDto->leagueSeasonId,
                    'league_id'        => $viewTableBestClubSeasonDto->leagueId,
                    'type_game_id'     => $viewTableBestClubSeasonDto->typeGameId,
                ],
                [
                    'club_id'             => $viewTableBestClubSeasonDto->clubId,
                    'season_id'           => $viewTableBestClubSeasonDto->seasonId,
                    'league_season_id'    => $viewTableBestClubSeasonDto->leagueSeasonId,
                    'league_id'           => $viewTableBestClubSeasonDto->leagueId,
                    'points'              => $viewTableBestClubSeasonDto->points,
                    'type_game_id'        => $viewTableBestClubSeasonDto->typeGameId,
                    'games_played'        => $viewTableBestClubSeasonDto->gamesPlayed,
                    'goals_scored'        => $viewTableBestClubSeasonDto->goalsScored,
                    'goals_conceded'      => $viewTableBestClubSeasonDto->goalsConceded,
                    'goals_diff'          => $viewTableBestClubSeasonDto->goalsDiff,
                    'wins'                => $viewTableBestClubSeasonDto->wins,
                    'yellow_cards'        => $viewTableBestClubSeasonDto->yellowCards,
                    'red_cards'           => $viewTableBestClubSeasonDto->redCards,
                    'avg_ball_possession' => $viewTableBestClubSeasonDto->avgBallPossession,
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
        $result = ViewTableBestClubsSeason::query();

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

        if (isset($filters['section_game_id'])) {
            $result = $result->where('section_game_id', $filters['section_game']);
        } else {
            $sectionGameAllGame = FilterTable::sectionGame()->allGame()->first();
            $result = $result->where('section_game_id', $sectionGameAllGame->id);
        }

        $order = isset($filters['order']) ? FilterTable::find($filters['order'])->sub_name : 'points';
        $limit = isset($filters['limit']) && $filters['limit'] > 0 ? $filters['limit'] : 10;

        return $result->orderBy($order)->limit($limit)->get();
    }
}
