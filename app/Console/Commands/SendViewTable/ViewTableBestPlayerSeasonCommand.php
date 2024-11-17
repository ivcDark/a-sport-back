<?php

namespace App\Console\Commands\SendViewTable;

use App\Dto\ViewTableBestPlayerSeasonDto;
use App\Models\FilterTable;
use App\Models\Season;
use App\Service\ViewTableBestPlayerSeasonService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ViewTableBestPlayerSeasonCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send-view-table:best-player-season';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Заполнение таблицы view_table_best_player_seasons';

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
                    $result = DB::select('CALL GetBestPlayerSeason(?, ?)', [$leagueSeason->id, $gameType->sub_name]);
                    foreach ($result as $item) {
                        $dto = new ViewTableBestPlayerSeasonDto([
                            'clubId'         => $item->club_id,
                            'seasonId'       => $season->id,
                            'leagueId'       => $leagueSeason->league_id,
                            'leagueSeasonId' => $leagueSeason->id,
                            'sectionGameId'  => null,
                            'typeGameId'     => $gameType->id,
                            'playerId'       => $item->player_id,
                            'gamesPlayed'    => $item->games_played,
                            'goals'          => $item->goals,
                            'assist'         => $item->assists,
                            'yellowCards'    => $item->yellow_cards,
                            'redCards'       => $item->red_cards,
                            'rating'         => 0.0
                        ]);
                        $model = (new ViewTableBestPlayerSeasonService())->updateOrCreate($dto);
                    }

                }

            }
        }

        $this->info('Работа окончена');
    }
}
