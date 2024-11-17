<?php

namespace App\Service;

use App\Models\League;
use App\Models\LeagueSeason;
use App\Models\Season;
use App\Models\ViewTableBestPlayerSeason;
use Illuminate\Support\Facades\DB;

class ViewTableService
{

    public function __construct()
    {

    }

    public function get()
    {
        $sectionGameId = '4da5b355-dfca-423a-8c8d-4fc0bec1a0ad';
        $typeGameId = 'e9f35d63-3fca-4df4-b99b-b0119575f667';
        $seasonModel = Season::where('title', '2024/2025')->first();
        $leagueModel = League::where('country_id', '9cc18f41-db44-4698-8c17-e5e57951fd51')->where('name', 'Premier League')->first();
        $leagueSeasonModel = LeagueSeason::where('league_id', $leagueModel->id)->where('season_id', $seasonModel->id)->first();

        return ViewTableBestPlayerSeason::select([
                'players.fio as player_name',
                'players.number as player_number',
                'players.image as player_image',
                'clubs.name as club_name',
                'view_table_best_player_seasons.goals as player_goals',
                'view_table_best_clubs_seasons.goals_scored as club_goals',
                DB::raw('ROUND(COALESCE(view_table_best_player_seasons.goals * 100.0 / NULLIF(view_table_best_clubs_seasons.goals_scored, 0), 0), 1) as goals_ratio_percent')
            ])
            ->leftJoin('view_table_best_clubs_seasons', 'view_table_best_clubs_seasons.club_id', '=', 'view_table_best_player_seasons.club_id')
            ->leftJoin('players', 'players.id', '=', 'view_table_best_player_seasons.player_id')
            ->leftJoin('clubs', 'clubs.id', '=', 'view_table_best_clubs_seasons.club_id')
            ->where('view_table_best_clubs_seasons.section_game_id', $sectionGameId)
            ->where('view_table_best_player_seasons.type_game_id', $typeGameId)
            ->where('view_table_best_clubs_seasons.league_season_id', $leagueSeasonModel->id)
            ->where('view_table_best_player_seasons.league_season_id', $leagueSeasonModel->id)
            ->orderByDesc('goals_ratio_percent')
            ->orderByDesc('player_goals')
            ->orderByDesc('club_goals')
            ->orderBy('player_name')
            ->limit(10)
            ->get();
    }
}
