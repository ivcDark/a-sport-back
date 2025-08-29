<?php

namespace App\Console\Commands\Parse\Flashscore;

use App\Models\Country;
use App\Models\League;
use App\Models\LeagueSeason;
use App\Models\Season;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

class GetLeagueSeasons extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'flashscore:get-league-seasons {--league= : ID лиги (FlashScore)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'FlashScore - получение сезонов в лиге';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $startTime = microtime(true);

        if ($this->option('league')) {
            $leagueModels = League::where('flashscore_id', $this->option('league'))->get();
        } else {
            $countryModel = Country::where('id', '9f2952bf-dbd9-4df6-8e28-77e943d53e6c')->first();
            $leagueModels = $countryModel->leagues;
        }

        $this->info('Запрашиваем по всем лигам по очереди');

        $seasons = [];

        try {
            DB::beginTransaction();

            $countLeagues = $leagueModels->count();
            $this->info("Всего лиг: " . $countLeagues);
            $cntL = 1;

            foreach ($leagueModels as $league) {
                $this->info("$cntL/$countLeagues");
                $cntL++;
                if ($league->slug == 'uefa-europa-cup-women') {
                    continue;
                }

                $this->info("Отправили запрос по лиге: https://www.flashscore.com/football/{$league->country->slug}/{$league->slug}/archive/");
                $result = Http::
                    timeout(30)
                    ->withHeaders([
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                        'Referer' => 'https://www.flashscore.com/',
                        'Origin' => 'https://www.flashscore.com',
                        'Accept' => '*/*',
                    ])
                    ->withoutRedirecting()
                    ->retry(3, 3000)
                    ->get("https://www.flashscore.com/football/{$league->country->slug}/{$league->slug}/archive/");

                if ($result->status() == 301) {
                    $result = Http::
                        timeout(30)
                        ->withHeaders([
                            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                            'Referer' => 'https://www.flashscore.com/',
                            'Origin' => 'https://www.flashscore.com',
                            'Accept' => '*/*',
                        ])
                        ->withoutRedirecting()
                        ->retry(3, 3000)
                        ->get("https://www.flashscore.com/{$league->slug}/archive/");
                }

                if ($result->status() == 404) {
                    $this->warn("Не смогли найти турнир: {$league->slug}. Запрос: https://www.flashscore.com/football/{$league->country->slug}/{$league->slug}/archive/");
                    continue;
                }

                if ($result->status() == 200) {
                    $crawler = new Crawler($result->body());

                    $rows = $crawler->filter('.archive__row');

                    $last_season_format = null;

                    foreach ($rows as $row) {
                        $rowCrawler = new Crawler($row);

                        $link = $rowCrawler->filter('.archive__season a');
                        if ($link->count() === 0) {
                            continue;
                        }

                        $seasonText = trim($link->text());
                        $href = $link->attr('href');

                        $seasonTitle = null;
                        $yearStart = null;
                        $yearEnd = null;

                        // Пытаемся вытащить YYYY/YYYY
                        if (preg_match('/(\d{4})\/(\d{4})/', $seasonText, $match)) {
                            $seasonTitle = $match[1] . '/' . $match[2];
                            $yearStart = (int) $match[1];
                            $yearEnd = (int) $match[2];
                            $last_season_format = 'double';
                        }
                        // Пытаемся вытащить одиночный год YYYY
                        elseif (preg_match('/\b(\d{4})\b/', $seasonText, $match)) {
                            $seasonTitle = $match[1];
                            $yearStart = (int) $match[1];
                            $yearEnd = (int) $match[1];
                            $last_season_format = 'single';
                        }
                        // Если ничего не нашли — применим текущий формат
                        else {
                            $currentYear = (int) date('Y');
                            if ($last_season_format === 'double') {
                                $seasonTitle = "$currentYear/" . ($currentYear + 1);
                                $yearStart = $currentYear;
                                $yearEnd = $currentYear + 1;
                            } else {
                                $seasonTitle = (string) $currentYear;
                                $yearStart = $yearEnd = $currentYear;
                            }
                        }

                        $seasons[] = [
                            'league_id' => $league->id,
                            'season_text' => $seasonText,
                            'season_title' => str_replace('/', '-', $seasonTitle),
                            'year_start' => $yearStart,
                            'year_end' => $yearEnd,
                        ];
                    }
                } else {
                    throw new \Exception("Произошла ошибка во время отправки запроса (ответ не 200). Response: {$result->body()}");
                }

                $this->info("Массив с сезонами в лиге {$league->name} сформирован");
            }

            foreach ($seasons as $season) {
                $seasonModel = Season::updateOrCreate(
                    [
                        'title' => $season['season_title'],
                        'year_start' => $season['year_start'],
                        'year_end' => $season['year_end'],
                    ],
                    [
                        'title' => $season['season_title'],
                        'year_start' => $season['year_start'],
                        'year_end' => $season['year_end'],
                    ]
                );

                $leagueSeasonModel = LeagueSeason::updateOrCreate(
                    [
                        'league_id' => $season['league_id'],
                        'season_id' => $seasonModel->id,
                    ],
                    [
                        'league_id' => $season['league_id'],
                        'season_id' => $seasonModel->id,
                    ]
                );
            }
        } catch (\Exception $exception) {
            DB::rollBack();
            $this->error($exception->getMessage());
        }

        DB::commit();

        $duration = round(microtime(true) - $startTime, 2); // ⏱️ Конец таймера
        $this->info("Команда выполнена за {$duration} секунд");
    }

}
