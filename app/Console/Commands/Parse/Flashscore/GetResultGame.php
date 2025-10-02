<?php

namespace App\Console\Commands\Parse\Flashscore;

use App\Models\Club;
use App\Models\ClubLeague;
use App\Models\Country;
use App\Models\Game;
use App\Models\GameResult;
use App\Models\GameRound;
use App\Models\GameStage;
use App\Models\League;
use App\Models\LeagueSeason;
use App\Models\Player;
use App\Models\PlayerClub;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Symfony\Component\Console\Command\Command as CommandAlias;
use Symfony\Component\DomCrawler\Crawler;

class GetResultGame extends Command
{
    protected $signature = 'flashscore:get-game-result {--league_season= : ID лиги сезона}';

    protected $description = 'FlashScore - получение результатов в лиге';
    private $gameStages;
    private $gameRounds;
    private $leagueSeasonModel;

    public function handle()
    {
        $this->gameStages = GameStage::all();
        $this->gameRounds = GameRound::all();

        DB::beginTransaction();

        try {
            if ($this->option('league_season')) {
                $this->error('Необходимо указать параметр --league_season');

                $this->leagueSeasonModel = LeagueSeason::where('id', $this->option('league_season'))->first();
                $this->info("Получаем список матчей");
                $games = $this->getGameResults();
                $this->info("Вставляем данные в БД");
                $this->insertGameResult($games, $this->leagueSeasonModel->id);
            } else {
                $leagues = League::whereIn('id',
                    [
                        '9f4fb238-c393-4a67-b8e4-fcc0fad2e5dd',
                        '9f4fb238-c898-4bbc-94b9-ec9391bb3013',
                        '9f4fb238-e235-4e84-bd4a-8e8054aecc08',
                    ]
                )
                    ->get();

                foreach ($leagues as $leagueModel) {
                    $this->leagueSeasonModel = LeagueSeason::where('league_id', $leagueModel->id)->where('season_id', '9f517377-0b23-446e-b440-92865bc5d68a')->first();
                    $this->info("Получаем список матчей");
                    $games = $this->getGameResults();
                    $this->info("Вставляем данные в БД");
                    $this->insertGameResult($games, $this->leagueSeasonModel->id);
                }
            }

            DB::commit();
            $this->info("Готово!");
            return CommandAlias::SUCCESS;
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error($e->getMessage());
            $this->error("Line: " . $e->getLine());
            return CommandAlias::FAILURE;
        }
    }

    private function getGameResults(): array
    {
        $this->info("Отправляем запрос для получения seasonId на Flashscore");

        $countrySlug = mb_strtolower($this->leagueSeasonModel->league->country->name);
        $leagueSlug = $this->leagueSeasonModel->league->slug;
        $seasonTitle = $this->leagueSeasonModel->season->title;

        $url = "https://www.flashscore.com/football/$countrySlug/$leagueSlug-$seasonTitle/results/";
        $result = Http::withoutVerifying()
            ->timeout(30)
            ->retry(3, 3000)
            ->get($url);

        if ($result->status() !== 200) {
            $this->error($result->body());
            throw new \Exception("Ошибка во время формирования URL: {$url}");
        }

        $crawler = new Crawler($result->body());
        $scriptContent = $crawler->filter('script')->reduce(function (Crawler $node, $i) {
            return strpos($node->text(), "cjs.initialFeeds['results']") !== false;
        })->text();

        $seasonIdPattern = "/seasonId\s*:\s*(\d+)/";
        preg_match($seasonIdPattern, $scriptContent, $seasonIdMatches);
        $seasonId = $seasonIdMatches[1];

        $this->info("ID получили: {$seasonId}");

        $pattern = "/cjs\.initialFeeds\['results'\]\s*=\s*\{\s*data:\s*`(.+?)`/s";
        preg_match($pattern, $scriptContent, $matches);

        $this->info("Декодируем результаты матчей с первой страницы");

        $results = $this->decodeStrGameResult($matches[1]);

        $this->info("Успешно! Теперь будем выгружать и декодировать матчи с других страниц");

        for ($i = 1; $i <= 4; $i++) {
            $this->info("Отправим запрос на страницу $i");
            $urlGames = "https://2.flashscore.ninja/2/x/feed/tr_1_{$this->leagueSeasonModel->league->country->flashscore_id}_{$this->leagueSeasonModel->league->flashscore_id}_{$seasonId}_{$i}_4_en_1";
            $result = Http::withoutVerifying()
                ->withHeaders([
                    "User-Agent" => "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36",
                    "x-fsign" => "SW9D1eZo",
                    "Accept" => "*/*",
                    "Origin" => "https://www.flashscore.com",
                    "Sec-Fetch-Site" => "cross-site",
                    "Sec-Fetch-Mode" => "cors",
                    "Sec-Fetch-Dest" => "empty",
                    "Referer" => "https://www.flashscore.com",
                    "Accept-Encoding" => "gzip, deflate, br, zstd",
                    "Accept-Language" => "ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7",
                ])
                ->timeout(30)
                ->retry(3, 3000)
                ->get($urlGames);

            if ($result->status() !== 200) {
                $this->error($result->body());
                throw new \Exception("Ошибка во время формирования URL: {$url}");
            }

            if ($result->body() == "") {
                $this->info("Страница пустая, значит список матчей закончился");
                return $results;
            }

            $this->info("Получили результат со страницы $i");
            $this->info("Декодируем результат со страницы $i");

            $results = array_merge($results, $this->decodeStrGameResult($result->body()));

            $this->info("Декодирование завершено");
        }

        return $results;
    }

    private function decodeStrGameResult(string $str): array
    {
        $results = [];
        $stage_title = null;

        // Разбиваем по ¬~
        $blocks = explode('¬~', $str);
        foreach ($blocks as $block) {
            // Стадия турнира
            if (preg_match('/ZA÷([^¬]+)¬/', $block, $m)) {
                $stage_title = trim($m[1]);
                continue;
            }

            // Матч
            if (!str_starts_with($block, 'AA÷')) continue;

            $block = mb_substr($block, 3); // убрать AA÷

            $code = $round = $home_team = $home_team_id = $away_team = $away_team_id = $time_start = '';
            $home_goals = $away_goals = null;

            if (preg_match('/^([^¬]+)¬/', $block, $m)) $code = $m[1];
            if (preg_match('/AE÷([^¬]+)¬/', $block, $m)) $home_team = $m[1];
            if (preg_match('/PX÷([^¬]+)¬/', $block, $m)) $home_team_id = $m[1];
            if (preg_match('/AF÷([^¬]+)¬/', $block, $m)) $away_team = $m[1];
            if (preg_match('/PY÷([^¬]+)¬/', $block, $m)) $away_team_id = $m[1];
            if (preg_match('/ER÷([^¬]+)¬/', $block, $m)) $round = $m[1];
            if (preg_match('/AD÷([^¬]+)¬/', $block, $m)) $time_start = $m[1];
            if (preg_match('/AU÷(\d+)¬/', $block, $m)) $away_goals = $m[1];
            if (preg_match('/AT÷(\d+)¬/', $block, $m)) $home_goals = $m[1];

            $leg_number = 1;

            if (preg_match('/AM÷([^¬]+)¬/', $block, $m)) {
                $text = $m[1];
                if (stripos($text, 'Aggregate:') !== false) {
                    $leg_number = 2;
                }
            }

            if (!$stage_title || !$round) continue;

            if ($home_goals == null && $away_goals == null) continue;

            $results[] = [
                'code' => $code,
                'round' => $round,
                'stage_title' => $stage_title,
                'time_start' => $time_start,
                'home_team' => $home_team,
                'home_team_id' => $home_team_id,
                'away_team' => $away_team,
                'away_team_id' => $away_team_id,
                'home_goals' => $home_goals,
                'away_goals' => $away_goals,
                'leg_number' => $leg_number,
            ];
        }

        return $results;
    }

    private function insertGameResult(array $data, string $leagueSeasonId)
    {
        foreach ($data as $game) {
            $clubHome = Club::where('flashscore_id', $game['home_team_id'])->first();
            $clubGuest = Club::where('flashscore_id', $game['away_team_id'])->first();

            if (!$clubHome) {
                $dataClub = [
                    'name' => $game['home_team'],
                    'id' => $game['home_team_id'],
                    'slug' => null,
                    'image' => null,
                ];
                $clubHome = $this->createClub($dataClub);
                $this->warn("Клуба {$game['home_team']} не было. Мы его создали");
//                throw new \Exception("Клуб не найден: " . json_encode($game));
            }

            if (!$clubGuest) {
                $dataClub = [
                    'name' => $game['away_team'],
                    'id' => $game['away_team_id'],
                    'slug' => null,
                    'image' => null,
                ];
                $clubGuest = $this->createClub($dataClub);
                $this->warn("Клуба {$game['away_team']} не было. Мы его создали");
//                throw new \Exception("Клуб не найден: " . json_encode($game));
            }

            $stageTournir = explode(":", $game['stage_title']);
            $stageTournir = trim($stageTournir[1]);
            $gameStageModel = $this->gameStages->where('title', $stageTournir)->first();
            $gameRoundModel = $this->gameRounds->where('title', $game['round'])->first();

            if (!$gameStageModel) {
                throw new \Exception("Не найдена стадия '{$stageTournir}'. Матч: " . json_encode($game));
            }
            if (!$gameRoundModel) {
                throw new \Exception("Не найден тур '{$game['round']}'. Матч: " . json_encode($game));
            }

            $gameModel = Game::updateOrCreate([
                'league_season_id' => $leagueSeasonId,
                'club_home_id' => $clubHome->id,
                'club_guest_id' => $clubGuest->id,
                'flashscore_id' => $game['code']
            ], [
                'league_season_id' => $leagueSeasonId,
                'club_home_id' => $clubHome->id,
                'club_guest_id' => $clubGuest->id,
                'flashscore_id' => $game['code'],
                'time_start' => $game['time_start'],
                'game_stage_id' => $gameStageModel->id,
                'game_round_id' => $gameRoundModel->id,
                'leg_number' => $game['leg_number'],
            ]);

            GameResult::updateOrCreate([
                'game_id' => $gameModel->id
            ], [
                'game_id' => $gameModel->id,
                'home_goals' => $game['home_goals'],
                'guest_goals' => $game['away_goals']
            ]);

            $this->info("Матч {$game['code']} записан");
        }
    }

    private function createClub(array $data)
    {
        $clubModel = Club::updateOrCreate(
            [
                'name' => $data['name'],
                'flashscore_id' => $data['id'],
                'country_id' => $this->leagueSeasonModel->league->country->id,
            ],
            [
                'name' => $data['name'],
                'full_name' => $data['name'],
                'slug' => $data['slug'],
                'flashscore_id' => $data['id'],
                'logo' => $data['image'],
                'country_id' => $this->leagueSeasonModel->league->country->id,
            ]
        );

        $clubLeagueModel = ClubLeague::updateOrCreate(
            [
                'club_id' => $clubModel->id,
                'league_season_id' => $this->leagueSeasonModel->id,
            ],
            [
                'club_id' => $clubModel->id,
                'league_season_id' => $this->leagueSeasonModel->id,
            ]
        );

        return $clubModel;
    }

}
