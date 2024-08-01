<?php

namespace App\Console\Commands\Parse;

use App\Console\Commands\Parse\Service\Soccer24\ClubService;
use App\Console\Commands\Parse\Service\Soccer24\GameProtocolService;
use App\Console\Commands\Parse\Service\Soccer24\GameResultService;
use App\Console\Commands\Parse\Service\Soccer24\GameStatisticService;
use App\Console\Commands\Parse\Service\Soccer24\PlayerService;
use App\Models\Club;
use App\Models\ClubLeague;
use App\Models\Country;
use App\Models\Game;
use App\Models\League;
use App\Models\LeagueSeason;
use App\Models\Season;
use Illuminate\Console\Command;

class Soccer24 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'parse:soccer24 {--clubs : Загружать команды}
    {--statistic : Загружать статистику}
    {--game_result : Загружать матчи}
    {--player : Загружать игроков}
    {--protocol : Протоколы матчей}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Парсинг soccer24.com';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $countryModel = Country::where('name', 'Россия')->first();
        $leagueModel = League::where('country_id', $countryModel->id)->where('name', 'Премьер-лига')->first();
//        $leagueModel = League::where('country_id', $countryModel->id)->where('name', 'ФНЛ')->first();
        $seasonModel = Season::where('title', '2023/2024')->first();
        $leagueSeasonModel = LeagueSeason::where('league_id', $leagueModel->id)->where('season_id', $seasonModel->id)->first();

        $this->info('Страна: ' . $countryModel->name);
        $this->info('Лига: ' . $leagueModel->name);
        $this->info('Сезон: ' . $seasonModel->title);

        if ($this->option('clubs')) {
            $this->info('Начинаем загружать команды');

            ClubService::start($leagueSeasonModel);

            $this->info('Загрузка команд завершена');
            return 1;
        } elseif ($this->option('game_result')) {
            $this->info('Начинаем загружать матчи');

            $season = str_replace('/', '-', $seasonModel->title);
            $url = "https://www.soccer24.com/ru/russia/premier-league-{$season}/results/";

            GameResultService::start($url, $leagueSeasonModel->id);

            $this->info('Загрузка матчей завершена');
            return 1;
        } elseif ($this->option('statistic')) {
            $this->info('Начинаем загружать статистику матчей');

            $games = Game::where('league_season_id', $leagueSeasonModel->id)->get();

            foreach ($games as $game) {
                GameStatisticService::start($game);
            }

            $this->info('Загрузка статистики матчей завершена');
            return 1;
        } elseif ($this->option('player')) {
            $this->info('Начинаем загружать игроков из матчей');

            $clubLeagues = ClubLeague::where('league_season_id', $leagueSeasonModel->id)->get();

            foreach ($clubLeagues as $clubLeague) {
                $club = Club::where('id', $clubLeague->club_id)->first();
                PlayerService::start($club);
            }

            $this->info('Загрузка игроков завершена');
            return 1;
        } elseif ($this->option('protocol')) {
            $this->info('Начинаем загружать протоколы матчей');

            $games = Game::where('league_season_id', $leagueSeasonModel->id)->get();
            $games = Game::where('id', '9ca56f8c-d7e0-4eb2-911b-451d626b0965')->get();

            foreach ($games as $game) {
                GameProtocolService::start($game);
            }

            $this->info('Загрузка протоколов завершена');
            return 1;
        } else {
            $this->info('Для работы необходимо передать какой-либо ключ');
            return 1;
        }
    }



}
