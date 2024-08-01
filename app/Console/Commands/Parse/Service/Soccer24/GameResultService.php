<?php

namespace App\Console\Commands\Parse\Service\Soccer24;

use App\Models\Club;
use App\Models\Game;
use App\Models\ModelIntegration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

class GameResultService
{
    public static function start(string $url, string $leagueSeasonId): void
    {
        $games = self::loadGameResults($url);
        self::insertGameResult($games, $leagueSeasonId);
    }

    private static function loadGameResults(string $url): array
    {
        $countryId = 158;

        $result = Http::withoutVerifying()
            ->get($url);
        $crawler = new Crawler($result->body());
        $scriptContent = $crawler->filter('script')->reduce(function (Crawler $node, $i) {
            return strpos($node->text(), "cjs.initialFeeds['results']") !== false;
        })->text();

        $seasonIdPattern = "/seasonId\s*:\s*(\d+)/";
        preg_match($seasonIdPattern, $scriptContent, $seasonIdMatches);
        $seasonId = $seasonIdMatches[1];

        $pattern = "/cjs\.initialFeeds\['results'\]\s*=\s*\{\s*data:\s*`(.+?)`/s";
        preg_match($pattern, $scriptContent, $matches);

        $leagueIdPattern = "/ZEE÷([^¬]+)¬/";
        preg_match($leagueIdPattern, $matches[1], $leagueIdMatches);
        $leagueId = $leagueIdMatches[1];

        $results = self::decodeStrGameResult($matches[1]);

        for ($i = 1; $i <= 4; $i++) {
            $result = Http::withoutVerifying()
                ->withHeaders([
                    "Host" => "local-s24.flashscore.ninja",
                    "Connection" => "keep-alive",
                    "sec-ch-ua" => "\"Not/A)Brand\";v=\"8\", \"Chromium\";v=\"126\", \"Google Chrome\";v=\"126\"",
                    "sec-ch-ua-platform" => "Windows",
                    "sec-ch-ua-mobile" => "?0",
                    "User-Agent" => "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36",
                    "x-fsign" => "SW9D1eZo",
                    "Accept" => "*/*",
                    "Origin" => "https://www.soccer24.com",
                    "Sec-Fetch-Site" => "cross-site",
                    "Sec-Fetch-Mode" => "cors",
                    "Sec-Fetch-Dest" => "empty",
                    "Referer" => "https://www.soccer24.com/",
                    "Accept-Encoding" => "gzip, deflate, br, zstd",
                    "Accept-Language" => "ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7",
                ])
                ->get("https://local-s24.flashscore.ninja/1023/x/feed/tr_1_{$countryId}_{$leagueId}_{$seasonId}_{$i}_4_ru_2");

            if ($result->body() == "") {
                return $results;
            }

            $results = array_merge($results, self::decodeStrGameResult($result->body()));
        }

        return $results;
    }

    private static function decodeStrGameResult(string $str): array
    {
        $results = [];
        $pattern = '/AA÷(.*?)¬~/s';
        if (preg_match_all($pattern, $str, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $block) {
                $block = $block[1];
                // В каждом блоке ищем необходимые данные
                $code = '';
                $home_team = '';
                $away_team = '';
                $home_goals = '';
                $away_goals = '';
                $round = '';
                $time_start = '';

                // Код между AA÷ и ¬
                if (preg_match('/^([^¬]+)¬/', $block, $m)) {
                    $code = $m[1];
                }

                // Название команды хозяев между AE÷ и ¬
                if (preg_match('/AE÷([^¬]+)¬/', $block, $m)) {
                    $home_team = $m[1];
                }

                // Название команды гостей между AF÷ и ¬
                if (preg_match('/AF÷([^¬]+)¬/', $block, $m)) {
                    $away_team = $m[1];
                }

                // Название тура между ER÷ и ¬
                if (preg_match('/ER÷([^¬]+)¬/', $block, $m)) {
                    $round = $m[1];
                }

                // Отметка времени начала матча AD÷ и ¬
                if (preg_match('/AD÷([^¬]+)¬/', $block, $m)) {
                    $time_start = $m[1];
                }

                // Количество голов команды хозяев между AH÷ и ¬
                if (preg_match('/AH÷(\d+)¬/', $block, $m)) {
                    $away_goals = $m[1];
                }

                // Количество голов команды гостей между AG÷(\d+)¬
                if (preg_match('/AG÷(\d+)¬/', $block, $m)) {
                    $home_goals = $m[1];
                }

                // Добавляем данные в результаты, если все поля найдены
                if ($code && $home_team && $away_team && $home_goals !== '' && $away_goals !== '' && $round && $time_start !== '') {
                    $results[] = [
                        'code' => $code,
                        'round' => $round,
                        'time_start' => $time_start,
                        'home_team' => $home_team,
                        'away_team' => $away_team,
                        'home_goals' => $home_goals,
                        'away_goals' => $away_goals,
                    ];
                }
            }
        }

        return $results;
    }

    private static function insertGameResult(array $data, string $leagueSeasonId): bool
    {
        try {
            DB::beginTransaction();

            foreach ($data as $game) {
                $clubHome = Club::where('name', $game['home_team'])->first();
                $clubGuest = Club::where('name', $game['away_team'])->first();

                $gameModel = Game::updateOrCreate(
                    [
                        'league_season_id' => $leagueSeasonId,
                        'club_home_id' => $clubHome->id,
                        'club_guest_id' => $clubGuest->id,
                        'tour' => $game['round']
                    ],
                    [
                        'league_season_id' => $leagueSeasonId,
                        'club_home_id' => $clubHome->id,
                        'club_guest_id' => $clubGuest->id,
                        'tour' => $game['round'],
                        'time_start' => $game['time_start'],
                    ]
                );

                ModelIntegration::firstOrCreate(
                    [
                        'model' => 'game',
                        'model_id' => $gameModel->id,
                        'type_integration' => 'soccer_24',
                    ],
                    [
                        'model' => 'game',
                        'model_id' => $gameModel->id,
                        'type_integration' => 'soccer_24',
                        'integration_id' => $game['code'],
                    ]
                );

                \App\Models\GameResult::updateOrCreate(
                    [
                        'game_id' => $gameModel->id
                    ],
                    [
                        'game_id' => $gameModel->id,
                        'home_goals' => $game['home_goals'],
                        'guest_goals' => $game['away_goals'],
                    ]
                );
            }

            DB::commit();
        } catch (\Exception $exception) {
            Log::error("Ошибка в методе GameResultService->insertGameResult()");
            Log::error($exception->getMessage());
            Log::error($exception->getTraceAsString());
            DB::rollBack();
            return false;
        }

        return true;
    }
}
