<?php

namespace App\Console\Commands\Parse\Flashscore;

use App\Models\Club;
use App\Models\ClubLeague;
use App\Models\Country;
use App\Models\Game;
use App\Models\GameResult;
use App\Models\LeagueSeason;
use App\Models\Player;
use App\Models\PlayerClub;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

class GetResultGame extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'flashscore:get-game-result {--league_season= : ID лиги сезона}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'FlashScore - получение результатов в лиге';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        DB::beginTransaction();

        try {
            if ($this->option('league_season')) {

                $this->info("Формируем список игр и их результаты");

                $games = $this->getGameResults();

                $this->info("Список готов. Приступаем к insert в БД");

                if ($this->insertGameResult($games, $this->option('league_season'))) {
                    $this->info('Загрузка игр завершена');
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
        }

    }

    private function getGameResults(): array
    {
        $this->info("Отправляем запрос для получения seasonId на Flashscore");
        $leagueSeasonModel = LeagueSeason::where('id', $this->option('league_season'))->first();
        $countrySlug = mb_strtolower($leagueSeasonModel->league->country->name);
        $leagueSlug = $leagueSeasonModel->league->slug;
        $seasonTitle = $leagueSeasonModel->season->title;

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
            $urlGames = "https://2.flashscore.ninja/2/x/feed/tr_1_{$leagueSeasonModel->league->country->flashscore_id}_{$leagueSeasonModel->league->flashscore_id}_{$seasonId}_{$i}_4_en_1";
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
        $pattern = '/AA÷(.*?)¬~/s';
        if (preg_match_all($pattern, $str, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $block) {
                $block = $block[1];
                // В каждом блоке ищем необходимые данные
                $code = '';
                $home_team = '';
                $home_team_id = '';
                $away_team = '';
                $away_team_id = '';
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

                // ID Flashscore между PX÷ и ¬
                if (preg_match('/PX÷([^¬]+)¬/', $block, $m)) {
                    $home_team_id = $m[1];
                }

                // Название команды гостей между AF÷ и ¬
                if (preg_match('/AF÷([^¬]+)¬/', $block, $m)) {
                    $away_team = $m[1];
                }

                // ID Flashscore между PY÷ и ¬
                if (preg_match('/PY÷([^¬]+)¬/', $block, $m)) {
                    $away_team_id = $m[1];
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
                        'home_team_id' => $home_team_id,
                        'away_team' => $away_team,
                        'away_team_id' => $away_team_id,
                        'home_goals' => $home_goals,
                        'away_goals' => $away_goals,
                    ];
                }
            }
        }

        return $results;
    }

    private function insertGameResult(array $data, string $leagueSeasonId)
    {
        foreach ($data as $game) {
            $clubHome = Club::where('flashscore_id', $game['home_team_id'])->first();
            $clubGuest = Club::where('flashscore_id', $game['away_team_id'])->first();

            if ($clubHome == null || $clubGuest == null) {
                $this->error("home_team_id = {$game['home_team_id']} | away_team_id = {$game['away_team_id']}");
                throw new \Exception("clubHome или clubGuest не найден в БД.");
            }

            $gameModel = Game::updateOrCreate(
                [
                    'league_season_id' => $leagueSeasonId,
                    'club_home_id' => $clubHome->id,
                    'club_guest_id' => $clubGuest->id,
                    'tour' => $game['round'],
                    'flashscore_id' => $game['code']
                ],
                [
                    'league_season_id' => $leagueSeasonId,
                    'club_home_id' => $clubHome->id,
                    'club_guest_id' => $clubGuest->id,
                    'tour' => $game['round'],
                    'time_start' => $game['time_start'],
                    'flashscore_id' => $game['code']
                ]
            );

            GameResult::updateOrCreate(
                [
                    'game_id' => $gameModel->id
                ],
                [
                    'game_id' => $gameModel->id,
                    'home_goals' => $game['home_goals'],
                    'guest_goals' => $game['away_goals'],
                ]
            );

            $this->info("Записали игру {$game['code']}");
        }

        $this->info("Все игры записали");
    }

}
