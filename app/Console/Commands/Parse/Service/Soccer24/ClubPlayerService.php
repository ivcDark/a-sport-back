<?php

namespace App\Console\Commands\Parse\Service\Soccer24;

use App\Models\Club;
use App\Models\ClubLeague;
use App\Models\Country;
use App\Models\League;
use App\Models\LeagueSeason;
use App\Models\ModelIntegration;
use App\Models\Player;
use App\Models\Season;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

class ClubPlayerService
{

    public static function start(Club $club)
    {
        $strPlayers = self::loadPlayersGame($club);
        $arrPlayers = self::decodeStrPlayersGame($strPlayers);
        self::insertPlayers($arrPlayers, $club);
    }

    private static function loadPlayersGame(Club $club): string
    {
        $result = Http::withoutVerifying()
            ->get("https://www.soccer24.com/ru/team/{$club->slug}/{$club->soccer24Id}/squad/");

        return $result->body();
    }

    private static function decodeStrPlayersGame(string $str): array
    {
        $crawler = new Crawler($str);
        $players = [];
        $crawler->filter('#overall-all-table')->each(function (Crawler $node) use (&$players) {
            $node->filter('.lineupTable--soccer')->each(function (Crawler $node) use (&$players) {
                if ($node->filter('.lineupTable__title')->text() != 'Тренер') {
                    $node->filter('.lineupTable__row')->each(function (Crawler $node) use (&$players) {
                        $name = $node->filter('a.lineupTable__cell--name')->text();
                        $countryName = $node->filter('.lineupTable__cell--flag')->attr('title');
                        $href = $node->filter('a.lineupTable__cell--name')->attr('href');
                        $number = $node->filter('.lineupTable__cell--jersey')->text();

                        preg_match('/\/ru\/player\/([\w-]+)\/([\w-]+)\//', $href, $matches);
                        $slug = $matches[1];
                        $id = $matches[2];

                        $players[] = [
                            'name' => $name,
                            'countryName' => $countryName,
                            'href' => $href,
                            'number' => $number,
                            'slug' => $slug,
                            'id' => $id,
                        ];
                    });
                }
            });
        });

        return $players;
    }

    private static function insertPlayers(array $players, Club $club): bool
    {
        $oldListPlayers = Player::where('club_id', $club->id)->get();

        foreach ($players as $player) {
            $country = Country::firstOrCreate(
                [
                    'name' => $player['countryName']
                ],
                [
                    'name' => $player['countryName']
                ]
            );

            try {
                DB::beginTransaction();

                $playerModel = Player::updateOrCreate(
                    [
                        'fio' => $player['name'],
                        'slug' => $player['slug'],
                        'country_id' => $country->id,
                        'club_id' => $club->id,
                    ],
                    [
                        'fio' => $player['name'],
                        'slug' => $player['slug'],
                        'country_id' => $country->id,
                        'club_id' => $club->id,
                        'number' => $player['number'] == '' ? null : $player['number'],
                        'in_club' => true,
                    ]
                );

                $modelIntegration = ModelIntegration::updateOrCreate(
                    [
                        'model' => 'player',
                        'model_id' => $playerModel->id,
                        'type_integration' => 'soccer_24',
                    ],
                    [
                        'model' => 'player',
                        'model_id' => $playerModel->id,
                        'type_integration' => 'soccer_24',
                        'integration_id' => $player['id'],
                    ]
                );

                dump($modelIntegration->id);

                if ($modelIntegration == null) {
                    dump("player: " . $playerModel->fio . " model_id: " . $playerModel->id);
                }

                $oldListPlayers = $oldListPlayers->reject(function ($player) use ($playerModel) {
                    return $player->id == $playerModel->id;
                });

                DB::commit();
            } catch (\Exception $exception) {
                Log::error("Ошибка в методе PlayerService->insertPlayers()");
                Log::error($exception->getMessage());
                DB::rollBack();
                return false;
            }
        }

        foreach ($oldListPlayers as $player) {
            $player->in_club = false;
            $player->save();
        }

        return true;
    }
}
