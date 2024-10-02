<?php

namespace App\Console\Commands\Parse\ApiFootball;

use App\Dto\ClubDto;
use App\Dto\PlayerDto;
use App\Models\LeagueSeason;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class Club extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api_football:club';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Парсинг api_football - клубы';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $jsonLeagues = file_get_contents('leagues.log');
        $arrLeagues = json_decode($jsonLeagues, true);

//        $leagues = \App\Models\League::all();
//        dump($leagues->count());

        $leagues = \App\Models\League::whereNotIn('id', $arrLeagues)->get();
        $leagues = \App\Models\League::where('id', '9cc1a42e-4363-4f4d-babb-5881ed14a528')->get();
//        dd($leagues->count());

        foreach ($leagues as $league) {
            $this->info("Лига: " . $league->name);
            $parse = new \App\Parse\ApiFootball\Club($league->apiFootballId);
            $arrClubs = $parse->start()->toArray();
            $leagueSeason = LeagueSeason::where('league_id', $league->id)->where('season_id', 'cc211e20-81a4-4a29-824a-0d1b6958ae36')->first();

            if (isset($arrClubs['error']) && $arrClubs['error'] == 404) {
                continue;
            }

            $this->info("В ресурсе найдено клубов: " . count($arrClubs));

            $countModelClubs = 0;
            foreach ($arrClubs as $club) {
                $this->info("Пишем в базу клуб: " . $club['team_name'] . ". Страна: " . $club['team_country']);
                $team_name = str_replace('Hong Kong, China', 'Hong Kong', $club['team_country']);
                $countryModel = \App\Models\Country::where('name', $team_name)->first();
                $dataToDto = [
                    'name' => $club['team_name'],
                    'fullName' => $club['team_name'],
                    'code' => Str::slug($club['team_name']),
                    'countryId' => $countryModel == null ? null : $countryModel->id,
                    'leagueSeasonId' => $leagueSeason->id,
                    'apiName' => $club['team_name'],
                    'apiId' => $club['team_key'],
                    'logo' => $club['team_badge'],
                ];
                $dto = new ClubDto($dataToDto);
                $clubModel = (new \App\Service\ClubService('apiFootball'))->updateOrCreate($dto);
                $this->info($clubModel != null ? "Ok" : "Error");
                $countModelClubs++;

                if ($clubModel != null) {
                    $this->info("В клубе найдено игроков: " . count($club['players']));

                    $countModelPlayers = 0;
                    foreach ($club['players'] as $player) {
                        $this->info("Пишем в базу игрока: " . $player['player_complete_name']);

                        $dataToDto = [
                            'fio' => $player['player_complete_name'],
                            'number' => $player['player_number'],
                            'clubId' => $clubModel->id,
                            'countryId' => null,
                            'slug' => Str::slug($player['player_complete_name']),
                            'birthday' => $player['player_birthdate'],
                            'position' => $player['player_type'],
                            'fieldName' => $player['player_name'],
                            'apiName' => $player['player_complete_name'],
                            'apiId' => $player['player_key'],
                            'logo' => $player['player_image'],
                        ];
                        $dto = new PlayerDto($dataToDto);
                        $playerModel = (new \App\Service\PlayerService('apiFootball'))->updateOrCreate($dto);

                        $countModelPlayers++;
                    }

                    $this->info("Для клуба {$clubModel->name} всего игроков: " . count($club['players']));
                    $this->info("В базу записали: " . $countModelPlayers);
                }

            }

            $this->info("Для лиги {$league->name} всего клубов: " . count($arrClubs));
            $this->info("В базу записали: " . $countModelClubs);

            $jsonLeagues = file_get_contents('leagues.log');
            $arrLeagues = json_decode($jsonLeagues, true);
            $arrLeagues[] = $league->id;
            file_put_contents('leagues.log', json_encode($arrLeagues));
        }








    }
}
