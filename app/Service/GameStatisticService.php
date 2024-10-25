<?php

namespace App\Service;

use App\Dto\GameStatisticDto;
use Illuminate\Support\Facades\DB;

class GameStatisticService
{
    private ?string $typeIntegration;
    private string $nameModel;

    public function __construct($typeIntegration = null)
    {
        $this->typeIntegration = $typeIntegration;
        $this->nameModel = 'game_statistics';
    }

    public static function getTypeIndicator(string $nameIndicator): ?string
    {
        $data = [
            'Shots Total' => 'shots_total',
            'Shots On Goal' => 'shots_on_goal',
            'Shots Off Goal' => 'shots_off_goal',
            'Shots Blocked' => 'shots_blocked',
            'Shots Inside Box' => 'shots_inside_box',
            'Shots Outside Box' => 'shots_outside_box',
            'Fouls' => 'fouls',
            'Corners' => 'corners',
            'Offsides' => 'offsides',
            'Ball Possession' => 'ball_possession',
            'Yellow Cards' => 'yellow_cards',
            'Red Cards' => 'red_cards',
            'Saves' => 'saves',
            'Passes Total' => 'passes_total',
            'Passes Accurate' => 'passes_accurate',
        ];

        return $data[$nameIndicator] ?? null;
    }

    public function create()
    {

    }

    public function updateOrCreate(GameStatisticDto $gameStatisticDto): ?\App\Models\GameStatistic
    {
        $gameStatistic = null;

        try {
            DB::beginTransaction();

            $gameStatistic = \App\Models\GameStatistic::updateOrCreate(
                [
                    'game_id'        => $gameStatisticDto->gameId,
                    'club_id'        => $gameStatisticDto->clubId,
                    'section_game'   => $gameStatisticDto->sectionGame,
                    'type_indicator' => $gameStatisticDto->typeIndicator,
                    'value'          => $gameStatisticDto->value,
                ],
                [
                    'game_id'        => $gameStatisticDto->gameId,
                    'club_id'        => $gameStatisticDto->clubId,
                    'section_game'   => $gameStatisticDto->sectionGame,
                    'type_indicator' => $gameStatisticDto->typeIndicator,
                    'value'          => $gameStatisticDto->value,
                ]
            );

            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
            dd($exception->getMessage());
        }

        return $gameStatistic;
    }

    public function get(array $filters)
    {
//        $limit = isset($filters['limit']) && $filters['limit'] > 0 ? $filters['limit'] : 20;
//
//        return Game::query()
//            ->when(isset($filters['tour']), function ($query) use ($filters) {
//                $query->where('tour', $filters['tour']);
//            })
//            ->when(isset($filters['date']), function ($query) use ($filters) {
//                $startPeriod = Carbon::parse($filters['date'])->startOfDay()->timestamp;
//                $endPeriod = Carbon::parse($filters['date'])->endOfDay()->timestamp;
//                $query->whereBetween('time_start', [$startPeriod, $endPeriod]);
//            })
//            ->when(!isset($filters['date']), function ($query) use ($filters) {
//                $startPeriod = Carbon::now()->startOfDay()->timestamp;
//                $endPeriod = Carbon::now()->endOfDay()->timestamp;
//                $query->whereBetween('time_start', [$startPeriod, $endPeriod]);
//            })
//            ->when(isset($filters['league_id']), function ($query) use ($filters) {
//                $season = Season::where('id', $filters['season'] ?? 'cc211e20-81a4-4a29-824a-0d1b6958ae36')->first();
//                $league = League::where('id', $filters['league_id'])->first();
//                $leagueSeason = LeagueSeason::where('season_id', $season->id)->where('league_id', $league->id)->first();
//                $query->where('league_season_id', $leagueSeason->id);
//            })
//            ->when(isset($filters['country_id']), function ($query) use ($filters) {
//                $season = Season::where('id', $filters['season'] ?? 'cc211e20-81a4-4a29-824a-0d1b6958ae36')->first();
//                $leagueIds = League::where('country_id', $filters['country_id'])->pluck('id')->toArray();
//                $leagueSeasonIds = LeagueSeason::where('season_id', $season->id)->whereIn('league_id', $leagueIds)->pluck('id')->toArray();
//                $query->whereIn('league_season_id', $leagueSeasonIds);
//            })
//            ->orderBy('time_start', 'desc')
//            ->paginate($limit);
    }
}
