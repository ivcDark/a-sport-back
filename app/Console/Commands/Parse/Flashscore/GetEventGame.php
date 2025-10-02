<?php

namespace App\Console\Commands\Parse\Flashscore;

use App\Models\Country;
use App\Models\Event;
use App\Models\Game;
use App\Models\League;
use App\Models\LeagueSeason;
use App\Models\Player;
use App\Models\PlayerClub;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\DomCrawler\Crawler;

class GetEventGame extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'flashscore:get-event-game {--league_season= : ID лиги сезона} {--game= : (all - Для всех матчей)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'FlashScore - получение событий матчей в сезоне';
    private $leagueSeasonModel;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        DB::beginTransaction();

        try {
            if ($this->option('league_season')) {
                $this->leagueSeasonModel = LeagueSeason::where('league_id', $this->option('league_season'))->first();
                $games = Game::where('league_season_id', $this->leagueSeasonModel->id)->get();
                $this->start($games);
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
                    $this->leagueSeasonModel = LeagueSeason::where('league_id', $league->id)->where('season_id', '9f517377-0b23-446e-b440-92865bc5d68a')->first();
                    $games = Game::where('league_season_id', $this->leagueSeasonModel->id);
                    if (!$this->option('game') && $this->option('game') != 'all') {
                        $games = $games->whereBetween('time_start', [Carbon::now()->subMinutes(200)->timestamp, Carbon::now()->addMinutes(200)->timestamp]);
                    }
                    $games = $games->get();
                    $this->start($games);
                }
            }

            DB::commit();
            return 1;
        } catch (\Exception $exception) {
            DB::rollBack();
            $this->error("Message: " . $exception->getMessage());
            $this->error("Line: " . $exception->getLine());
            return false;
        }
    }

    private function start($games): void
    {
        if (count($games) == 0) {
            $this->error("Нет матчей для LeagueSeason: " . $this->leagueSeasonModel->id . ". Проверьте/загрузите игры.");
            return;
        }

        $this->info("Формируем массив с событиями в матчах и пишем в файлы");

        $this->getGameEvents($games);

        $this->info("События по матчам готовы. Приступаем к insert в БД");

        if ($this->insertData($games)) {
            $this->info('Загрузка событий игр завершена');
        }
    }

    private function getGameEvents($games): array
    {
        $events = [];
        foreach ($games as $game) {
            $eventsGame = [];
            $this->info("Отправляем запрос на получение событий по матчу {$game->flashscore_id}");

            $result = Http::withHeaders([
                    'x-fsign' => 'SW9D1eZo'
                ])
                ->timeout(30)
                ->retry(3, 3000)
                ->get("https://2.flashscore.ninja/2/x/feed/df_sui_1_{$game->flashscore_id}");

            if ($result->status() == 200) {
                // Разделение строки на секции по таймам
                $sections = preg_split('/AC÷(1st Half|2nd Half)¬/', $result->body(), -1, PREG_SPLIT_DELIM_CAPTURE);
                $current_period = null;

                foreach ($sections as $section) {
                    if (preg_match('/(1st Half|2nd Half)/', $section, $match)) {
                        $current_period = $match[1];
                    } elseif ($current_period !== null) {
                        preg_match_all('/III÷(.*?)¬.*?IA÷(.*?)¬IB÷(.*?)¬(.*?)¬~/', $section, $matches, PREG_SET_ORDER);

                        foreach ($matches as $match) {
                            $event = [
                                'тайм' => $current_period,
                                'id' => $match[1],
                                'поле_IA' => $match[2],
                                'поле_IB' => $match[3],
                            ];

                            // Разделение строки параметров на пары ключ-значение
                            $params = explode('¬', $match[4]);
                            $counter = ['IE' => 1, 'IF' => 1, 'IU' => 1, 'ICT' => 1, 'IK' => 1, 'IM' => 1];
                            foreach ($params as $param) {
                                if (preg_match('/^(IE|IF|IU|ICT|IK|IM)÷(.*)$/', $param, $pair)) {
                                    $key = $pair[1];
                                    $value = $pair[2];

                                    if (in_array($key, ['IF', 'IK', 'IM', 'IU'])) {
                                        $event[$key . '_' . $counter[$key]] = $value;
                                        $counter[$key]++;
                                    } else {
                                        $event[$key] = $value;
                                    }
                                }
                            }

                            $eventsGame[] = $event;
                        }
                    }
                }

            } else {
                $this->error("Произошла ошибка во время отправление запроса");
                $this->error("Message: " . $result->body());
                throw new \Exception("Произошла ошибка во время отправление запроса");
            }

            $events[$game->flashscore_id] = $eventsGame;

            Storage::put("events/events_{$game->flashscore_id}.json", json_encode($eventsGame, JSON_UNESCAPED_UNICODE));

            $this->info("Записали в файл events_{$game->flashscore_id}.json");
        }

        return $events;
    }


    private function insertData($games): bool
    {
        foreach ($games as $game) {
            Event::where('game_id', $game->id)->delete();
            $this->info("Будем записывать игру {$game->flashscore_id}");
            $json = Storage::get("events/events_{$game->flashscore_id}.json");
            $data = json_decode($json, true);

            if (count($data) === 0) {
                throw new \Exception("Массив с событиями пустой. Проверьте почему");
            }

            foreach ($data as $item) {
                $player = Player::where('flashscore_id', $item['IM_1'])->first();
                if ($player == null) {
                    $dataNewPlayer = $this->getDataPlayerHttp($item['IU_1'], $item['IM_1']);
                    $player = $this->setPlayerToDb($dataNewPlayer);
                    if ($player == null) {
                        throw new \Exception("Не удалось создать футболиста, которого нет в БД - ID '{$item['IM_1']}' ({$item['IF_1']})");
                    }
                    $this->warn("Не было футболиста {$player->fio}. Добавили его в БД. Теперь прикрепим к клубу");

                    $playerClubModel = PlayerClub::where('player_id', $player->id)->get();
                    foreach ($playerClubModel as $playerClub) {
                        $playerClub->in_club = false;
                        $playerClub->save();
                    }

                    $this->info("Добавили связь игрока с клубом");
                }

                $club = PlayerClub::where('player_id', $player->id)->first();
                if ($club == null) {
                    $club = PlayerClub::updateOrCreate(
                        [
                            'player_id' => $player->id,
                            'club_id' => $game->club_home_id,
                        ],
                        [
                            'player_id' => $player->id,
                            'club_id' => $game->club_home_id,
                            'in_club' => false,
                        ]
                    );
//                    throw new \Exception("Не нашли клуб у игрока {$item['IM_1']}");
                    $this->warn("Не нашли клуб у игрока {$item['IM_1']}");
                }

                $type = null;
                $sectionGame = null;

                if ($item['IK_1'] == 'Substitution - Out') {
                    $type = 'ZAMENA_S_POLYA';
                } elseif ($item['IK_1'] == 'Substitution - In') {
                    $type = 'ZAMENA_NA_POLE';
                } elseif ($item['IK_1'] == 'Goal') {
                    $type = 'GOL';
                } elseif ($item['IK_1'] == 'Assistance') {
                    $type = 'GOL_PAS';
                } elseif ($item['IK_1'] == 'Own goal') {
                    $type = 'AVTOGOL';
                } elseif ($item['IK_1'] == 'Penalty Kick') {
                    $type = 'PENALTI_UDAR';
                } elseif ($item['IK_1'] == 'Yellow Card') {
                    $type = 'YELLOW_CARD';
                } elseif ($item['IK_1'] == 'Red Card') {
                    $type = 'RED_CARD';
                }

                if ($item['тайм'] == '1st Half') {
                    $sectionGame = 'ONE_PERIOD';
                } elseif ($item['тайм'] == '2nd Half') {
                    $sectionGame = 'TWO_PERIOD';
                } elseif ($item['тайм'] == 'Extra Time') {
                    $sectionGame = 'DOP_PERIOD';
                } elseif ($item['тайм'] == 'Penalties') {
                    $sectionGame = 'SERIYA_PENALTI';
                }

                if ($sectionGame == null) {
                    throw new \Exception("Не смогли определить section_game. Проверьте словарь. Искали: {$item['тайм']}");
                }

                if ($type != null) {
                    $minute = str_replace("'", '', $item['поле_IB']);
                    $minutes = explode('+', $minute);
                    $minute = (int) $minutes[0] + (isset($minutes[1]) ? (int) $minutes[1] : 0);
                    $minute = ($minute - 1) . ':' . '59';

                    $event = Event::firstOrCreate(
                        [
                            'game_id' => $game->id,
                            'club_id' => $club->club_id,
                            'player_id' => $player->id,
                            'type' => $type,
                            'minute' => $minute,
                            'section_game' => $sectionGame,
                        ],
                        [
                            'game_id' => $game->id,
                            'club_id' => $club->club_id,
                            'player_id' => $player->id,
                            'type' => $type,
                            'minute' => $minute,
                            'section_game' => $sectionGame,
                            'value' => 1,
                        ]
                    );
                } else {
                    throw new \Exception("Не смогли распознать тип события. Проверьте словарь с типами. Искалось: {$item['IK_1']}");
                }

                if (isset($item['IK_2'])) {
                    $type = null;

                    $player = Player::where('flashscore_id', $item['IM_2'])->first();
                    if ($player == null) {
                        $dataNewPlayer = $this->getDataPlayerHttp($item['IU_2'], $item['IM_2']);
                        $player = $this->setPlayerToDb($dataNewPlayer);
                        if ($player == null) {
                            throw new \Exception("Не удалось создать футболиста, которого нет в БД - ID '{$item['IM_2']}' ({$item['IF_2']})");
                        }
                        $this->warn("Не было футболиста {$player->fio}. Добавили его в БД. Теперь прикрепим к клубу");

                        $playerClubModel = PlayerClub::where('player_id', $player->id)->get();
                        foreach ($playerClubModel as $playerClub) {
                            $playerClub->in_club = false;
                            $playerClub->save();
                        }
                        PlayerClub::updateOrCreate(
                            [
                                'player_id' => $player->id,
                                'club_id' => $game->club_home_id,
                            ],
                            [
                                'player_id' => $player->id,
                                'club_id' => $game->club_home_id,
                                'in_club' => $dataNewPlayer['team_id'] == $game->clubHome->flashscore_id,
                            ]
                        );

                        $this->info("Добавили связь игрока с клубом");
                    }

                    $club = PlayerClub::where('player_id', $player->id)->first();
                    if ($club == null) {
                        $club = PlayerClub::updateOrCreate(
                            [
                                'player_id' => $player->id,
                                'club_id' => $game->club_home_id,
                            ],
                            [
                                'player_id' => $player->id,
                                'club_id' => $game->club_home_id,
                                'in_club' => false,
                            ]
                        );
//                        throw new \Exception("Не нашли клуб у игрока {$item['IM_2']}");
                        $this->warn("Не нашли клуб у игрока {$item['IM_2']}");
                    }

                    if ($item['IK_2'] == 'Substitution - Out') {
                        $type = 'ZAMENA_S_POLYA';
                    } elseif ($item['IK_2'] == 'Substitution - In') {
                        $type = 'ZAMENA_NA_POLE';
                    } elseif ($item['IK_2'] == 'Goal') {
                        $type = 'GOL';
                    } elseif ($item['IK_2'] == 'Assistance') {
                        $type = 'GOL_PAS';
                    } elseif ($item['IK_2'] == 'Own goal') {
                        $type = 'AVTOGOL';
                    } elseif ($item['IK_2'] == 'Penalty Kick') {
                        $type = 'PENALTI_UDAR';
                    } elseif ($item['IK_2'] == 'Penalty') {
                        $type = 'PENALTI_ZABIL';
                    } elseif ($item['IK_2'] == 'Penalty missed') {
                        $type = 'PENALTI_NE_ZABIL';
                    } elseif ($item['IK_2'] == 'Yellow Card') {
                        $type = 'YELLOW_CARD';
                    } elseif ($item['IK_2'] == 'Red Card') {
                        $type = 'RED_CARD';
                    } elseif ($item['IK_2'] == 'Not on pitch') {
                        $type = 'NOT_ON_PITCH';
                    }

                    if ($type != null) {
                        $minute = str_replace("'", '', $item['поле_IB']);
                        $minutes = explode('+', $minute);
                        $minute = (int) $minutes[0] + (isset($minutes[1]) ? (int) $minutes[1] : 0);
                        $minute = ($minute - 1) . ':' . '59';

                        $event = Event::firstOrCreate(
                            [
                                'game_id' => $game->id,
                                'club_id' => $club->club_id,
                                'player_id' => $player->id,
                                'type' => $type,
                                'minute' => $minute,
                                'section_game' => $sectionGame,
                            ],
                            [
                                'game_id' => $game->id,
                                'club_id' => $club->club_id,
                                'player_id' => $player->id,
                                'type' => $type,
                                'minute' => $minute,
                                'section_game' => $sectionGame,
                                'value' => 1,
                            ]
                        );
                    } else {
                        throw new \Exception("Не смогли распознать тип события. Проверьте словарь с типами. Искалось: {$item['IK_2']}");
                    }
                }
            }

            $this->info("Игра {$game->flashscore_id} записана");
        }

        $this->info("Вся информация записана в БД");

        return true;
    }

    private function getDataPlayerHttp(string $url, string $playerId): array
    {
        $result = Http::
            timeout(30)
            ->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
            ])
            ->retry(3, 3000)
            ->get("https://www.flashscore.com{$url}");

        $this->info("Отправили запрос по игроку {$url} - {$playerId}");

        if ($result->status() == 200) {
            $html = (string) $result->getBody();
            $crawler = new Crawler($html);

            $info = [];

            // Имя игрока
            $info['name'] = $crawler->filter('h2[data-testid="wcl-scores-heading-02"]')->text('', false);

            // Страна игрока (schema.org ListItem)
            $country = $crawler->filter('li[itemtype="https://schema.org/ListItem"] span[itemprop="name"]')->last();
            if ($country->count()) {
                $info['country'] = $country->text();
            }

            // Позиция
            $positionNode = $crawler->filter('strong[data-testid="wcl-scores-simpleText-01"]');
            foreach ($positionNode as $node) {
                $text = trim($node->textContent);
                if (in_array($text, ['Goalkeeper', 'Defender', 'Midfielder', 'Forward', 'Coach'])) {
                    $info['position'] = $text;
                    break;
                }
            }

            // Общие данные (возраст, дата рождения, рыночная стоимость и т.д.)
            $crawler->filter('div.playerInfoItem')->each(function (Crawler $div) use (&$info) {
                $label = $div->filter('strong[data-testid="wcl-scores-simpleText-01"]')->first();
                if ($label->count()) {
                    $key = strtolower(str_replace(':', '', trim($label->text())));
                    $spans = $div->filter('span[data-testid="wcl-scores-simpleText-01"]');
                    switch ($key) {
                        case 'age':
                            $info['age'] = $spans->eq(0)->text('');
                            $info['birthdate'] = $spans->eq(1)->text('');
                            break;
                        case 'market value':
                            $info['market_value'] = $spans->eq(0)->text('');
                            break;
                        case 'contract expires':
                            $info['contract_expires'] = $spans->eq(0)->text('');
                            break;
                    }
                }
            });

            $teamLink = $crawler->filter('div.playerTeam a.playerInfoItem__link')->first();

            if ($teamLink->count()) {
                $href = $teamLink->attr('href'); // /team/botafogo-rj/jXzWoWa5/
                if (preg_match('#/team/([^/]+)/([^/]+)/#', $href, $matches)) {
                    $info['team_slug'] = $matches[1]; // botafogo-rj
                    $info['team_id'] = $matches[2];   // jXzWoWa5
                }

                $info['team_name'] = trim($teamLink->text(), '()'); // Botafogo RJ
            } else {
                $info['team_slug'] = null;
                $info['team_id'] = null;
                $info['team_name'] = null;
                $this->warn("У игрока {$info['name']} сейчас нет клуба");
//                throw new \Exception("Не смогли понять что за команда");
            }

            $this->info("Сформировали информацию по игроку");
        } else {
            $this->error("Произошла ошибка во время отправление запроса");
            $this->error("Message: " . $result->body());
            throw new \Exception("Произошла ошибка во время отправление запроса");
        }

        if (preg_match('#/player/([^/]+)/([^/]+)/#', $url, $matches)) {
            $info['slug'] = $matches[1];
            $info['id'] = $playerId;
        } else {
            $info['slug'] = "";
            $info['id'] = $playerId;
        }

        return $info;
    }

    private function setPlayerToDb(array $player): Player
    {
        $birthday = null;
        if (isset($player['birthdate'])) {
            $birthday = str_replace(['(', ')'], "", $player['birthdate']);
        }

        $countryModel = Country::where('name', $player['country'])->first();

        if ($countryModel != null) {
            $this->info("Искали страну `{$player['country']}` и нашли `{$countryModel->name}`");
        } else {
            $this->warn("Не нашли `{$player['country']}`. Нужно добавить эту страну и только потом скрипт заработает");
            throw new \Exception("Не нашли `{$player['country']}`. Нужно добавить эту страну и только потом скрипт заработает");
        }

        $playerModel = Player::updateOrCreate(
            [
                'slug' => $player['slug'],
                'flashscore_id' => $player['id'],
            ],
            [
                'fio' => $player['name'],
                'number' => null,
                'slug' => $player['slug'],
                'in_club' => true,
                'flashscore_id' => $player['id'],
                'country_id' => $countryModel->id,
                'birthday' => $birthday != null ? Carbon::parse($birthday)->format('Y-m-d') : null,
                'position' => $player['position'] ?? null,
            ]
        );

        $this->info("Игрок создан в БД: {'id' = $playerModel->id}");

        return $playerModel;
    }

}
