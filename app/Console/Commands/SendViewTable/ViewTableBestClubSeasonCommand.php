<?php

namespace App\Console\Commands\SendViewTable;

use App\Dto\ViewTableBestClubSeasonDto;
use App\Models\FilterTable;
use App\Models\Season;
use App\Service\ViewTableBestClubSeasonService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ViewTableBestClubSeasonCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send-view-table:best-club-season';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Заполнение таблицы view_table_best_clubs_seasons';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Начало работы');

        $seasons = Season::all();
        $gameTypes = FilterTable::homeGuest()->get();

        foreach ($seasons as $season) {
            foreach ($season->leagueSeasons as $leagueSeason) {
                foreach ($gameTypes as $gameType) {
                    $result = DB::select('CALL GetBestClubsSeason(?, ?, ?)', [$leagueSeason->id, $gameType->sub_name, 'full_game']);

                    foreach ($result as $item) {
                        $dto = new ViewTableBestClubSeasonDto([
                            'clubId'            => $item->club_id,
                            'seasonId'          => $season->id,
                            'leagueId'          => $leagueSeason->league_id,
                            'leagueSeasonId'    => $leagueSeason->id,
                            'typeGameId'        => $gameType->id,
                            'gamesPlayed'       => $item->games_played ?? 0,
                            'points'            => $item->total_points ?? 0,
                            'goalsScored'       => $item->goals_scored ?? 0,
                            'goalsConceded'     => $item->goals_conceded ?? 0,
                            'goalsDiff'         => $item->goal_difference ?? 0,
                            'wins'              => $item->wins ?? 0,
                            'yellowCards'       => $item->total_yellow_cards ?? 0,
                            'redCards'          => $item->total_red_cards ?? 0,
                            'avgBallPossession' => $item->avg_ball_possession ?? 0
                        ]);
                        $model = (new ViewTableBestClubSeasonService())->updateOrCreate($dto);
                    }
                }
            }
        }

        $this->info('Работа окончена');
    }
}
