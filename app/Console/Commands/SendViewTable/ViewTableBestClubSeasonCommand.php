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
        $sectionsGame = FilterTable::sectionGame()->get();

        foreach ($seasons as $season) {
            foreach ($season->leagueSeasons as $leagueSeason) {
                foreach ($sectionsGame as $sectionGame) {
                    $result = DB::select('CALL GetBestClubsSeason(?, ?)', [$leagueSeason->id, $sectionGame->sub_name]);

                    foreach ($result as $item) {
                        $dto = new ViewTableBestClubSeasonDto([
                            'clubId'            => $item->club_id,
                            'seasonId'          => $season->id,
                            'leagueId'          => $leagueSeason->league_id,
                            'leagueSeasonId'    => $leagueSeason->id,
                            'sectionGameId'     => $sectionGame->id,
                            'gamesPlayed'       => $item->games_played,
                            'points'            => $item->total_points,
                            'goalsScored'       => $item->goals_scored,
                            'goalsConceded'     => $item->goals_conceded,
                            'goalsDiff'         => $item->goal_difference,
                            'wins'              => $item->wins,
                            'yellowCards'       => $item->total_yellow_cards,
                            'redCards'          => $item->total_red_cards,
                            'avgBallPossession' => $item->avg_ball_possession
                        ]);
                        $model = (new ViewTableBestClubSeasonService())->updateOrCreate($dto);
                    }
                }
            }
        }

        $this->info('Работа окончена');
    }
}
