<?php

namespace App\Console\Commands\Parse\Betcity;

use App\Dto\ClubDto;
use App\Dto\GameDto;
use App\Dto\GamePlayerDto;
use App\Dto\GameResultDto;
use App\Dto\PlayerDto;
use App\Models\BetAbbreviatedType;
use App\Models\BetCompareType;
use App\Models\BetType;
use App\Models\LeagueSeason;
use App\Models\ModelIntegration;
use App\Models\Season;
use App\Parse\Betcity\Event;
use App\Service\GamePlayerService;
use App\Service\GameResultService;
use App\Service\GameService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Game extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'betcity:game';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Парсинг Betcity - матчи';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $listLeagues = ['74979'];

        foreach ($listLeagues as $league) {
            $dataLeague = (new \App\Parse\Betcity\Game($league))->start()->toArray();
            $leagueSeasonId = '9cc1eda6-0471-4ab6-8fd5-43ce152e80b2';

            foreach ($dataLeague['reply']['sports'][1]['chmps'][$league]['evts'] as $game) {
                $date = Carbon::createFromTimestamp($game['date_ev']);
                $dateAt = $date->copy()->subHours(4)->timestamp;
                $dateHt = $date->copy()->addHours(6)->timestamp;
                $clubNameHome = $game['name_ht'];
                $clubNameGuest = $game['name_at'];

                dump("game_id: {$game['id_ev']}, date_at: {$dateAt}, date_ht: {$dateHt}, clubNameHome: {$clubNameHome}, clubNameGuest: {$clubNameGuest}");

                $gameModel = DB::table('games')
                    ->select(
                        'games.id as game_id',
                        'club_home.name as club_home_name',
                        'club_guest.name as club_guest_name',
                        'games.tour as game_tour',
                        'games.time_start as game_start'
                    )
                    ->leftJoin('clubs as club_home', 'club_home.id', '=', 'games.club_home_id')
                    ->leftJoin('clubs as club_guest', 'club_guest.id', '=', 'games.club_guest_id')
                    ->where('games.league_season_id', $leagueSeasonId)
                    ->where('games.time_start', '>', $dateAt)
                    ->where('games.time_start', '<', $dateHt)
                    ->whereRaw("MATCH (club_home.name) AGAINST ('{$clubNameHome}' IN NATURAL LANGUAGE MODE)")
                    ->whereRaw("MATCH (club_guest.name) AGAINST ('$clubNameGuest' IN NATURAL LANGUAGE MODE)")
                    ->orderByRaw("
                        (MATCH (club_home.name) AGAINST ('Spartak Moscow' IN NATURAL LANGUAGE MODE) +
                         MATCH (club_guest.name) AGAINST ('Dynamo Moscow' IN NATURAL LANGUAGE MODE)) DESC
                    ")
                    ->first();

                $events = (new Event($game['id_ev']))->start()->toArray();

                $eventsGame = $events['reply']['sports']['1']['chmps'][$league]['evts'][$game['id_ev']]['ext'];

                foreach ($eventsGame as $event) {
                    $eventName = str_replace($clubNameHome, 1, $event['name']);
                    $eventName = str_replace($clubNameGuest, 2, $eventName);
                    $this->info("Ищем в БД BetType: " . $eventName);

                    $betTypeModel = BetType::where('name', $eventName)->first();

                    if ($betTypeModel != null) {
                        $this->info("Найдено в БД BetType: " . $eventName);
                        if (isset($event['rows'])) {
//                            foreach ($event['rows'] as $e_event) {
//                                dump($e_event['name']);
//                                dump($e_event['data']);
//                            }
                        } else {
//                            dump($event['data']);
                            $eventsGame = array_shift($event['data'])['blocks'];

                            foreach ($eventsGame as $nameAbbreviated => $dataAbbreviated) {
                                $this->info("__Ищем в БД BetAbbreviatedType: " . $nameAbbreviated);
                                $betAbbreviatedTypeModel = BetAbbreviatedType::where('name', $nameAbbreviated)->first();

                                if ($betAbbreviatedTypeModel !== null) {
                                    $this->info("__Найдено в БД BetAbbreviatedType: " . $nameAbbreviated);

                                    foreach ($dataAbbreviated as $nameCompareType => $dataBetCompare) {
                                        $this->info("__Ищем в БД BetCompareType: " . $nameCompareType);
                                        $betCompareType = BetCompareType::where('name', $nameCompareType)->first();

                                        if ($betCompareType !== null) {
                                            $this->info("__Найдено в БД BetCompareType: " . $nameCompareType);
                                        } else {
                                            $this->warn("__Не найдено в БД BetAbbreviatedType: " . $nameAbbreviated);
                                        }
                                    }

                                } else {
                                    $this->warn("__Не найдено в БД BetAbbreviatedType: " . $nameAbbreviated);
                                }
                            }
                        }
                    } else {
                        $this->warn("Не найдено в БД BetType: " . $eventName);
                    }

                }

                dd(123);

//                dump($gameModel);
            }

//            dd($dataLeague['reply']['sports'][1]['chmps'][$league]['evts']);
        }

        return true;
    }
}
