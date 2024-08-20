<?php

namespace App\Console\Commands\Parse\Service\Soccer24;

use App\Dto\PlayerDto;
use App\Models\Club;
use App\Models\Event;
use App\Models\Game;
use App\Models\GamePlayer;
use App\Models\ModelIntegration;
use App\Models\Player;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GamePlayerService
{
    private static Game $game;



    public static function start(Game $game)
    {
        self::$game = $game;

        $html = self::loadHtml($game);
        $data = self::parseStr($html);
        $result = self::insert($data);

        return $result;
    }

    public static function loadHtml(Game $game): string
    {
        $result = Http::withoutVerifying()
            ->get("https://1023.ds.lsapp.eu/pq_graphql?_hash=dlie&eventId={$game->soccer24Id}&projectId=1023");

        return $result->body();
    }

    public static function parseStr(string $str)
    {
        $data = [];
        $all = json_decode($str, true);

        foreach ($all['data']['findEventById']['eventParticipants'] as $command) {
            $players = $command['lineup']['players'];
            $typeCommand = $command['type']['side'];
            $startGroup = $command['lineup']['groups'][0]['playerIds'];
            $reserveGroup = $command['lineup']['groups'][1]['playerIds'];

            foreach ($players as $player) {
                $modelPlayer = null;

                $modelIntegrationPlayer = ModelIntegration::where('model', 'player')
                    ->where('type_integration', 'soccer_24')
                    ->where('integration_id', $player['id'])
                    ->first();

                if ($modelIntegrationPlayer ==null) {
                    $service = new PlayerService($player['participant']['url'], $player['id']);
                    $modelPlayer = $service->downloadHtml()->parseHtml()->insert()->getPlayer();
                } else {
                    $modelPlayer = Player::where('id', $modelIntegrationPlayer->model_id)->first();
                }

                $data[$typeCommand][] = [
                    'game_id' => self::$game->id,
                    'player_id' => $modelPlayer->id,
                    'is_start_group' => !(!in_array($player['id'], $startGroup)),
                    'is_reserve_group' => !(!in_array($player['id'], $reserveGroup)),
                    'is_injured_group' => false,
                    'is_best' => $player['rating'] == null ? null : $player['rating']['isBest'],
                    'rating' => $player['rating'] == null ? null : $player['rating']['value'],
                ];

            }

        }

        return $data;
    }

    public static function insert(array $data)
    {
        try {
            DB::beginTransaction();

            foreach ($data as $typeCommand => $items) {
                foreach ($items as $item) {
                    $gamePlayer = GamePlayer::updateOrCreate(
                        [
                            'game_id' => $item['game_id'],
                            'club_id' => $typeCommand == 'HOME' ? self::$game->club_home_id : self::$game->club_guest_id,
                            'player_id' => $item['player_id'],
                        ],
                        [
                            'game_id' => $item['game_id'],
                            'club_id' => $typeCommand == 'HOME' ? self::$game->club_home_id : self::$game->club_guest_id,
                            'player_id' => $item['player_id'],
                            'is_start_group' => $item['is_start_group'],
                            'is_reserve_group' => $item['is_reserve_group'],
                            'is_injured_group' => $item['is_injured_group'],
                            'is_best' => $item['is_best'] == null ? false : true,
                            'rating' => $item['rating'],
                        ]
                    );
                }
            }

            DB::commit();
        } catch (\Exception $exception) {
            Log::error("Ошибка в методе GamePlayerService->insert()");
            Log::error($exception->getMessage());
            DB::rollBack();
            return false;
        }

        return true;
    }
}
