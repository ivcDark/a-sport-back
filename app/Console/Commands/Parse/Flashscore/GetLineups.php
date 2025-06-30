<?php

namespace App\Console\Commands\Parse\Flashscore;

use App\Models\Club;
use App\Models\Coach;
use App\Models\Country;
use App\Models\ExternalMapping;
use App\Models\Game;
use App\Models\GamePlayer;
use App\Models\League;
use App\Models\Player;
use App\Models\PlayerClub;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\DomCrawler\Crawler;

class GetLineups extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'flashscore:get-lineups {--league_season= : ID лиги сезона}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'FlashScore - получение составов на матч';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        DB::beginTransaction();

        try {
            if ($this->option('league_season')) {

                $games = Game::where('league_season_id', $this->option('league_season'))->get();

                if (count($games) == 0) {
                    throw new \Exception("Игр не найдено в этом сезоне. Проверьте/загрузите игры.");
                }

                $this->info("Формируем список Lineups");

//                $this->getGameLineups($games);

                $this->info("Составы по матчам готовы. Приступаем к insert в БД");

                if ($this->insertData($games)) {
                    $this->info('Загрузка составов завершена');
                }

            } else {
                $this->error('Для работы необходимо указать лигу сезона (UUID)');
                return false;
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

    private function getGameLineups($games): array
    {
        $lineups = [];
        foreach ($games as $game) {
            $result = Http::
                timeout(30)
                ->retry(3, 3000)
                ->get("https://2.ds.lsapp.eu/pq_graphql?_hash=dlie2&eventId={$game->flashscore_id}&projectId=2");
            $this->info("Отправили запрос по матчу {$game->flashscore_id}");

            if ($result->status() == 200) {
                $this->info("Ответ получен");
                $data = json_decode($result->body(), true);
                $data = $data['data']['findEventById']['eventParticipants'];
                Storage::put("lineups/lineups_{$game->flashscore_id}.json", json_encode($data, JSON_UNESCAPED_UNICODE));

                $this->info("Записали в файл lineups_{$game->flashscore_id}.json");
            } else {
                $this->error("Произошла ошибка во время отправление запроса");
                $this->error("Message: " . $result->body());
                throw new \Exception("Произошла ошибка во время отправление запроса");
            }
        }

        $this->info("Записали всю информацию в файл");
        return $lineups;
    }

    private function insertData($games): bool
    {
        foreach ($games as $game) {
            $json = Storage::get("lineups/lineups_{$game->flashscore_id}.json");
            $data = json_decode($json, true);

            foreach ($data as $club) {
                $clubModel = $club['type']['side'] == 'HOME' ? $game->clubHome : $game->clubGuest;
                $players = $club['lineup']['players'];
                $groupStarting = $club['lineup']['groups'][0]['playerIds'];
                $groupSubstitutes = $club['lineup']['groups'][1]['playerIds'];

                foreach ($players as $player) {
                    if (array_search($player['id'], $groupStarting) !== false) {
                        $group = "Starting";
                    } elseif (array_search($player['id'], $groupSubstitutes) !== false) {
                        $group = "Substitutes";
                    } else {
                        $group = "Unknown";
                    }

                    if ($group == "Unknown") {
                        dump($club);
                        throw new \Exception("У игрока с FS ID '{$player['id']}' ({$player['listName']}) не смогли определить группу. Игра '{$game->flashscore_id}' Загрузка не может быть выполнена");
                    }

                    $playerModel = Player::where('flashscore_id', $player['id'])->first();

                    if ($playerModel == null) {
                        $dataNewPlayer = $this->getDataPlayerHttp($player['participant']['url'], $player['id']);
                        $playerModel = $this->setPlayerToDb($dataNewPlayer);
                        if ($playerModel == null) {
                            throw new Exception("Не удалось создать футболиста, которого нет в БД - ID '{$player['id']}' ({$player['listName']})");
                        }
                        $this->warn("Не было футболиста {$playerModel->fio}. Добавили его в БД. Теперь прикрепим к клубу");

                        $playerClubModel = PlayerClub::where('player_id', $playerModel->id)->get();
                        foreach ($playerClubModel as $playerClub) {
                            $playerClub->in_club = false;
                            $playerClub->save();
                        }
                        PlayerClub::updateOrCreate(
                            [
                                'player_id' => $playerModel->id,
                                'club_id' => $club['type']['side'] == 'HOME' ? $game->club_home_id : $game->club_guest_id,
                            ],
                            [
                                'player_id' => $playerModel->id,
                                'club_id' => $club['type']['side'] == 'HOME' ? $game->club_home_id : $game->club_guest_id,
                                'in_club' => $dataNewPlayer['team_id'] == $clubModel->flashscore_id,
                            ]
                        );

                        $this->info("Добавили связь игрока с клубом");
                    }

                    $gamePlayerModel = GamePlayer::updateOrCreate(
                        [
                            'game_id' => $game->id,
                            'club_id' => $club['type']['side'] == 'HOME' ? $game->club_home_id : $game->club_guest_id,
                            'player_id' => $playerModel->id,
                        ],
                        [
                            'game_id' => $game->id,
                            'club_id' => $club['type']['side'] == 'HOME' ? $game->club_home_id : $game->club_guest_id,
                            'player_id' => $playerModel->id,
                            'is_start_group' => $group == "Starting",
                            'is_reserve_group' => $group == "Substitutes",
                            'is_injured_group' => $group == "Injured",
                            'is_best' => $player['rating']['isBest'] ?? false,
                            'rating' => $player['rating']['value'] ?? null,
                            'number_player' => $player['number'] ?? null,
                        ]
                    );
                    $this->info("Игрок {$playerModel->fio} записан в матче");
                }

                $this->info("Клуб {$club['name']} обработан");
            }

            $this->info("Вся информация записана в БД");
        }

        return true;
    }

    private function getDataPlayerHttp(string $url, string $playerId): array
    {
        $result = Http::
            timeout(30)
            ->retry(3, 3000)
            ->get("https://www.flashscore.com/player/{$url}/{$playerId}/");
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
                if (in_array($text, ['Goalkeeper', 'Defender', 'Midfielder', 'Forward'])) {
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

        $info['slug'] = $url;
        $info['id'] = $playerId;

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
