<?php

namespace App\Console\Commands\Parse\Service\Soccer24;

use App\Models\Club;
use App\Models\Event;
use App\Models\Game;
use App\Models\ModelIntegration;
use App\Models\Player;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GameProtocolService
{
    public static function start(Game $game)
    {
        GamePlayers::start($game);


        $strProtocol = self::loadProtocolGame($game->soccer24Id);
        $arrProtocol = self::decodeStrProtocolGame($strProtocol);
        self::insertProtocol($arrProtocol, $game);
    }

    private static function loadProtocolGame(string $idGame): string
    {
        $result = Http::withHeaders([
            "Host" => "local-s24.flashscore.ninja",
            "Connection" => "keep-alive",
            "Pragma" => "no-cache",
            "Cache-Control" => "no-cache",
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
            ->withoutVerifying()
            ->get("https://local-s24.flashscore.ninja/1023/x/feed/df_sui_2_{$idGame}");

        return $result->body();
    }

    private static function decodeStrProtocolGame(string $str): array
    {
        // Разделение строки на секции по таймам
        $sections = preg_split('/AC÷(1-й тайм|2-й тайм)¬/', $str, -1, PREG_SPLIT_DELIM_CAPTURE);

        $events = [];
        $current_period = null;

        foreach ($sections as $section) {
            if (preg_match('/(1-й тайм|2-й тайм)/', $section, $match)) {
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

                            if (in_array($key, ['IF', 'IK', 'IM'])) {
                                $event[$key . '_' . $counter[$key]] = $value;
                                $counter[$key]++;
                            } else {
                                $event[$key] = $value;
                            }
                        }
                    }

                    $events[] = $event;
                }
            }
        }

        return $events;
    }

    private static function insertProtocol(array $data, Game $game): bool
    {
        try {
            DB::beginTransaction();

            foreach ($data as $item) {
                $modelIntegration = ModelIntegration::where('model', 'player')->where('integration_id', $item['IM_1'])->first();
                Log::info('IF_1 = ' . $item['IF_1']);
                Log::info('поле_IB = ' . $item['поле_IB']);
                Log::info('integration_id = ' . $item['IM_1']);
                Log::info('integration = ' . $modelIntegration->id);
                $player = Player::where('id', $modelIntegration->model_id)->where('in_club', true)->first();
                $club = Club::where('id', $player->club_id)->first();
                $type = null;
                $sectionGame = null;

                if ($item['IK_1'] == 'Замена (выходит)') {
                    $type = 'ZAMENA_S_POLYA';
                } elseif ($item['IK_1'] == 'Замена (уходит)') {
                    $type = 'ZAMENA_NA_POLE';
                } elseif ($item['IK_1'] == 'Гол') {
                    $type = 'GOL';
                } elseif ($item['IK_1'] == 'Голевой пас') {
                    $type = 'GOL_PAS';
                } elseif ($item['IK_1'] == 'Автогол') {
                    $type = 'AVTOGOL';
                } elseif ($item['IK_1'] == 'Пенальти') {
                    $type = 'PENALTI';
                } elseif ($item['IK_1'] == 'Желтая карточка') {
                    $type = 'YELLOW_CARD';
                } elseif ($item['IK_1'] == 'Красная карточка') {
                    $type = 'RED_CARD';
                }

                if ($item['тайм'] == '1-й тайм') {
                    $sectionGame = 'ONE_PERIOD';
                } elseif ($item['тайм'] == '2-й тайм') {
                    $sectionGame = 'TWO_PERIOD';
                } elseif ($item['тайм'] == 'Дополнительное время') {
                    $sectionGame = 'DOP_PERIOD';
                } elseif ($item['тайм'] == 'Серия пенальти') {
                    $sectionGame = 'SERIYA_PENALTI';
                }

                if ($type != null) {
                    $minute = str_replace("'", '', $item['поле_IB']);
                    $minutes = explode('+', $minute);
                    $minute = (int) $minutes[0] + (isset($minutes[1]) ? (int) $minutes[1] : 0);
                    $minute = ($minute - 1) . ':' . '59';

                    $event = Event::firstOrCreate(
                        [
                            'game_id' => $game->id,
                            'club_id' => $club->id,
                            'player_id' => $player->id,
                            'type' => $type,
                            'minute' => $minute,
                            'section_game' => $sectionGame,
                        ],
                        [
                            'game_id' => $game->id,
                            'club_id' => $club->id,
                            'player_id' => $player->id,
                            'type' => $type,
                            'minute' => $minute,
                            'section_game' => $sectionGame,
                            'value' => 1,
                        ]
                    );
                }
            }

            DB::commit();
        } catch (\Exception $exception) {
            Log::error("Ошибка в методе GameProtocolService->insertProtocol()");
            Log::error($exception->getMessage());
            DB::rollBack();
            dd($exception->getMessage());
            return false;
        }


        dd(111);

        return true;
    }
}
