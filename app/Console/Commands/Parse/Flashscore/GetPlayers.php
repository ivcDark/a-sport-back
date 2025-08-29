<?php

namespace App\Console\Commands\Parse\Flashscore;

use App\Models\Club;
use App\Models\ClubLeague;
use App\Models\Country;
use App\Models\League;
use App\Models\LeagueSeason;
use App\Models\Player;
use App\Models\PlayerClub;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

class GetPlayers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'flashscore:get-players {--club= : ID команды} {--league_season= : ID лиги сезона}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'FlashScore - получение игроков в клубе';

    private Club $clubModel;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        DB::beginTransaction();

        try {
            if ($this->option('club')) {

                $players = $this->getPlayers($this->option('club'));
                $this->info("Загружаем игроков в базу");

                if ($this->storePlayers($players)) {
                    $this->info('Загрузка игроков завершена');
                    return 1;
                }

            } elseif ($this->option('league_season')) {
                $clubLeagueModels = ClubLeague::where('league_season_id', $this->option('league_season'))->has('club')->get();

                foreach ($clubLeagueModels as $clubLeagueModel) {
                    $players = $this->getPlayers($clubLeagueModel->club->flashscore_id);

                    $this->info("Загружаем игроков в базу");

                    if ($this->storePlayers($players)) {
                        $this->info('Загрузка игроков завершена');
                    }
                }
            } else {
                $leagues = League::whereIn('id',
                    [
                        '9f4fb238-c393-4a67-b8e4-fcc0fad2e5dd',
                        '9f4fb238-c898-4bbc-94b9-ec9391bb3013',
                        '9f4fb238-e235-4e84-bd4a-8e8054aecc08',
                    ]
                )
                    ->get();
                foreach ($leagues as $league) {
                    $leagueSeasonModel = LeagueSeason::where('league_id', $league->id)
                        ->where('season_id', '9f517377-0b23-446e-b440-92865bc5d68a')
                        ->first();
                    $leagueClubs = ClubLeague::where('league_season_id', $leagueSeasonModel->id)->get();

                    foreach ($leagueClubs as $leagueClub) {
                        $players = $this->getPlayers($leagueClub->club->flashscore_id);

                        $this->info("Загружаем игроков в базу");

                        if ($this->storePlayers($players)) {
                            $this->info('Загрузка игроков завершена');
                        }
                    }
                }
            }

            DB::commit();
            return 1;
        } catch (\Exception $exception) {
            DB::rollBack();
            $this->error("Message: " . $exception->getMessage());
            $this->error("Line: " . $exception->getLine());
        }

    }

    private function getPlayers(string $clubFlashscoreId): ?array
    {
        $this->clubModel = Club::where('flashscore_id', $clubFlashscoreId)->first();
        if (!$this->clubModel) {
            $this->error("Клуб с flashscore_id {$clubFlashscoreId} не найден");
            return null;
        }

        $players = [];

        $this->info("Начинаем получать игроков клуба {$this->clubModel->name}");

        $this->info("URL: https://www.flashscore.com/team/{$this->clubModel->slug}/{$this->clubModel->flashscore_id}/squad/");

        try {
            $result = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Referer' => "https://www.flashscore.com/team/{$this->clubModel->slug}/{$this->clubModel->flashscore_id}/",
                'Cookie' => 'OptanonAlertBoxClosed=2025-06-15T12:31:20.578Z; _gcl_au=1.1.11386119.1749990681; eupubconsent-v2=CQTCk-AQTCk-AAcABBENBvFsAP_gAEPgAChQLSNT_G__bWlr-T73aftkeYxP99h77sQxBgbJE-4FzLvW_JwXx2E5NAzatqIKmRIAu3TBIQNlHJDURVCgaogVryDMaEyUgTNKJ6BkiBMRI2NYCFxvm4tjeQCY5vp991c1mB-t7dr83dzyy4hHn3a5_2S1WJCdAYetDfv8ZBKT-9IMd_x8v4v4_F7pE2-eS1n_pGvp6B9-YnM_9B299_bbffzPFcQqF_-_X_vf_n37v943n77v___BaAAEw0KiCMkiAEIlAwggQAKCsICKBAEAACQFEBACYMCnIGAC6wkQAgBQADBACAAEGAAIAABIAEIgAoAKBAABAIFAAGAAAIBAAwMAAYALAQCAAEB0DFMCCAQLABIzIoNICUABIICWyoQSAIEFcIQizwCCBETBQAAAgAFAQAAPBYDEkgJWJBAFxBNAAAQAABRAgQIpGjAEFAZstBeDJ9GRpgGBpgGaUwDIAiCMjJNiE37TDxyFEKAKEgCAA0AGUAe0BeY6AIADQAZQB7QF5koAIC8ykAMAGgB7QF5lAAIAygAA.f_wACHwAAAAA; _ga=GA1.1.715770512.1749990676; optimizelyEndUserId=oeu1749990697878r0.8471627211687167; _clck=774w4e%7C2%7Cfwu%7C0%7C1993; _clsk=q9vqov%7C1750130294951%7C1%7C0%7Cj.clarity.ms%2Fcollect; OptanonConsent=isGpcEnabled=0&datestamp=Tue+Jun+17+2025+19%3A19%3A15+GMT%2B0400+(%D0%A1%D0%B0%D0%BC%D0%B0%D1%80%D1%81%D0%BA%D0%BE%D0%B5+%D1%81%D1%82%D0%B0%D0%BD%D0%B4%D0%B0%D1%80%D1%82%D0%BD%D0%BE%D0%B5+%D0%B2%D1%80%D0%B5%D0%BC%D1%8F)&version=202505.1.0&browserGpcFlag=0&isIABGlobal=false&consentId=595d6a56-4f36-4f81-b7c2-1779d52cd758&interactionCount=1&isAnonUser=1&landingPath=NotLandingPage&groups=C0001%3A1%2CC0002%3A1%2CC0004%3A1%2CC0003%3A1%2CV2STACK42%3A1&hosts=H594%3A1%2CH481%3A1%2CH508%3A1%2CH233%3A1%2CH283%3A1%2CH404%3A1%2CH456%3A1%2CH560%3A1%2CH620%3A1%2CH561%3A1%2CH457%3A1%2CH286%3A1%2CH623%3A1%2CH507%3A1%2CH526%3A1%2CH317%3A1%2CH517%3A1%2Cocm%3A1%2CH3%3A1%2CH8%3A1%2CH371%3A1%2CH192%3A1%2CH12%3A1%2Cdrt%3A1%2CH463%3A1%2CH16%3A1%2CH21%3A1%2CH171%3A1%2CH25%3A1%2CH26%3A1%2CH31%3A1%2CH195%3A1%2Cxhy%3A1%2Crkk%3A1%2CH35%3A1%2CH598%3A1%2CH41%3A1%2CH350%3A1%2CH467%3A1%2CH42%3A1%2CH45%3A1%2CH46%3A1%2CH514%3A1%2Coiu%3A1%2Cifk%3A1%2CH499%3A1%2CH199%3A1%2CH485%3A1%2Cqvr%3A1%2CH213%3A1%2CH58%3A1%2CH60%3A1%2CH616%3A1%2CH64%3A1%2CH65%3A1%2CH66%3A1%2CH464%3A1%2CH68%3A1%2CH70%3A1%2CH74%3A1%2CH500%3A1%2CH292%3A1%2CH79%3A1%2CH81%3A1%2CH618%3A1%2CH85%3A1%2CH86%3A1%2CH87%3A1%2CH487%3A1%2CH89%3A1%2CH90%3A1%2CH91%3A1%2CH596%3A1%2CH96%3A1%2CH97%3A1%2CH478%3A1%2CH99%3A1%2CH518%3A1%2CH285%3A1%2CH106%3A1%2CH109%3A1%2Ctbv%3A1%2CH294%3A1%2CH112%3A1%2CH473%3A1%2CH575%3A1%2CH115%3A1%2CH217%3A1%2Ccch%3A1%2CH119%3A1%2CH121%3A1%2CH123%3A1%2CH125%3A1%2CH447%3A1%2CH127%3A1%2CH128%3A1%2CH129%3A1%2CH132%3A1%2CH205%3A1%2CH465%3A1%2Csey%3A1%2CH503%3A1%2CH138%3A1%2Cwiy%3A1%2CH179%3A1%2CH143%3A1%2Cygh%3A1%2CH513%3A1%2CH145%3A1%2CH146%3A1%2CH494%3A1%2CH150%3A1%2CH621%3A1%2CH562%3A1%2CH152%3A1%2CH154%3A1%2Cueh%3A1%2CH477%3A1%2CH156%3A1%2Csef%3A1%2CH207%3A1%2CH162%3A1%2CH208%3A1%2CH209%3A1%2CH166%3A1%2Cfrw%3A1%2Cefx%3A1%2CH211%3A1%2Caeg%3A1%2Cdlq%3A1&genVendors=V2%3A1%2C&intType=1&geolocation=NL%3BNH&AwaitingReconsent=false; pageviewCount=31; _ga_3NV6GD9ZTT=GS2.1.s1750173554$o12$g1$t1750173569$j45$l0$h0; _sg_b_v=8%3B9739%3B1750173558',
            ])
                ->timeout(40)
                ->retry(3, 4000)
                ->get("https://www.flashscore.com/team/{$this->clubModel->slug}/{$this->clubModel->flashscore_id}/squad/");
        } catch (\Exception $exception) {
            $this->error("Вероятного, не хватает данных для скачивания клуба");
            $this->error($exception->getMessage());
            return null;
        }


        if ($result->status() == 200) {
            $crawler = new Crawler($result->body());

            $crawler->filter('#overall-all-table')->each(function (Crawler $node) use (&$players) {
                $node->filter('.lineupTable--soccer')->each(function (Crawler $node) use (&$players) {
                    if ($node->filter('.lineupTable__title')->text() != 'Coach') {
                        $node->filter('.lineupTable__row')->each(function (Crawler $node) use (&$players) {
                            $name = $node->filter('a.lineupTable__cell--name')->text();
                            $countryName = $node->filter('.lineupTable__cell--flag')->attr('title');
                            $href = $node->filter('a.lineupTable__cell--name')->attr('href');
                            $number = $node->filter('.lineupTable__cell--jersey')->text();

                            preg_match('/player\/([\w-]+)\/([\w-]+)\//', $href, $matches);
                            $slug = $matches[1];
                            $id = $matches[2];

                            $countryModel = Country::where('name', $countryName)->first();

                            if ($countryModel != null) {
                                $this->info("Искали страну `$countryName` и нашли `$countryModel->name`");
                            } else {
                                $this->warn("Не нашли `$countryName`. Нужно добавить эту страну и только потом скрипт заработает");
                                throw new \Exception("Не нашли `$countryName`. Нужно добавить эту страну и только потом скрипт заработает");
                                return null;
                            }

                            $players[] = [
                                'name' => $name,
                                'countryName' => $countryName,
                                'country_id' => $countryModel->id,
                                'href' => $href,
                                'number' => $number,
                                'slug' => $slug,
                                'id' => $id,
                            ];
                        });
                    }
                });
            });

            $this->info("Игроков с сайта получили");
        } else {
            $this->error("Ошибка во время выгрузки данных из Flashscore");
            $this->error($result->body());
            return null;
        }

        return $players;
    }

    public function storePlayers(?array $players): bool
    {
        if ($players != null && count($players) > 0) {
            foreach ($players as $player) {
                $playerModel = Player::updateOrCreate(
                    [
                        'slug' => $player['slug'],
                        'flashscore_id' => $player['id'],
                    ],
                    [
                        'fio' => $player['name'],
                        'number' => $player['number'] == '' ? null : $player['number'],
                        'slug' => $player['slug'],
                        'in_club' => true,
                        'flashscore_id' => $player['id'],
                        'country_id' => $player['country_id'],
                    ]
                );

                $playerClubModel = PlayerClub::where('player_id', $playerModel->id)
                    ->get();
                foreach ($playerClubModel as $playerClub) {
                    $playerClub->in_club = false;
                    $playerClub->save();
                }
                PlayerClub::updateOrCreate(
                    [
                        'player_id' => $playerModel->id,
                        'club_id' => $this->clubModel->id,
                    ],
                    [
                        'player_id' => $playerModel->id,
                        'club_id' => $this->clubModel->id,
                        'in_club' => true,
                    ]
                );


                $this->info("Игрок создан в БД: {'id' = $playerModel->id}");
            }
        } else {
            $this->error("Массив с игроками пустой");
            return false;
        }

        return true;
    }

}
