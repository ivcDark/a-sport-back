<?php

namespace App\Console\Commands\Parse\Service\Soccer24;

use App\Models\Club;
use App\Models\ClubLeague;
use App\Models\League;
use App\Models\LeagueSeason;
use App\Models\ModelIntegration;
use App\Models\Season;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ClubService
{
    public static function start(LeagueSeason $leagueSeasonModel)
    {
        $loadClubs = self::loadClubs($leagueSeasonModel->soccer24Id);
        $parseClubs = self::decodeStrClubs($loadClubs);
        self::insertClubs($parseClubs, $leagueSeasonModel);
    }

    private static function loadClubs(string $idSeasonLeague)
    {
        $result = Http::withHeaders([
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
            "Accept-Language" => "ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7"
        ])
            ->withoutVerifying()
            ->get("https://local-s24.flashscore.ninja/1023/x/feed/{$idSeasonLeague}");

        return $result->body();
    }

    private static function decodeStrClubs(string $str): array
    {
        preg_match_all('/TN÷(.*?)¬.*?TI÷(.*?)¬.*?TIU÷(.*?)¬/', $str, $matches);

        $slugs = [];

        foreach ($matches[3] as $item) {
            $arr = explode('/', $item);
            $slugs[] = $arr[3];
        }

        return ['names' => $matches[1], 'ids' => $matches[2], 'slugs' => $slugs];
    }

    private static function insertClubs(array $data, LeagueSeason $leagueSeasonModel): bool
    {
        try {
            DB::beginTransaction();

            foreach ($data['names'] as $key => $clubName) {

                $clubModel = Club::updateOrCreate(
                    [
                        'name' => $clubName,
                        'slug' => $data['slugs'][$key],
                    ],
                    [
                        'name'      => $clubName,
                        'full_name' => $clubName,
                        'slug'      => $data['slugs'][$key],
                    ]
                );

                $clubLeagueModel = ClubLeague::firstOrCreate(
                    [
                        'club_id' => $clubModel->id,
                        'league_season_id' => $leagueSeasonModel->id,
                    ],
                    [
                        'club_id' => $clubModel->id,
                        'league_season_id' => $leagueSeasonModel->id,
                    ]
                );

                ModelIntegration::updateOrCreate(
                    [
                        'model' => 'club',
                        'model_id' => $clubModel->id,
                        'type_integration' => 'soccer_24',
                    ],
                    [
                        'model' => 'club',
                        'model_id' => $clubModel->id,
                        'type_integration' => 'soccer_24',
                        'integration_id' => $data['ids'][$key],
                    ]
                );
            }

            DB::commit();
        } catch (\Exception $exception) {
            Log::error("Ошибка в методе ClubService->insertCubs()");
            Log::error($exception->getMessage());
            DB::rollBack();
            return false;
        }

        return true;
    }
}
