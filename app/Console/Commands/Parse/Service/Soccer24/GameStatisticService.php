<?php

namespace App\Console\Commands\Parse\Service\Soccer24;

use App\Models\Event;
use App\Models\Game;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GameStatisticService
{
    public static function start(Game $game): void
    {
        $loadResults = self::loadStatistic($game->soccer24Id);
        $parseResults = self::decodeStrStatistic($loadResults);
        self::insertStatistic($parseResults, $game);
    }

    private static function loadStatistic(string $idGame)
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
            ->get("https://local-s24.flashscore.ninja/1023/x/feed/df_st_2_{$idGame}");

        return $result->body();
    }

    private static function decodeStrStatistic(string $str) {
        // Разбиваем строки на секции
        $sections = preg_split('/SE÷/', $str, -1, PREG_SPLIT_NO_EMPTY);
        $result = [];

        foreach ($sections as $section) {
            // Получаем название секции
            preg_match('/^(.*?)¬/', $section, $section_title);
            $title = $section_title[1];

            // Инициализируем массив для текущей секции
            if (!isset($result[$title])) {
                $result[$title] = [];
            }

            // Ищем данные в текущей секции
            preg_match_all('/SD÷(\d+)¬SG÷(.*?)¬SH÷(.*?)¬SI÷(.*?)¬~/', $section, $matches, PREG_SET_ORDER);

            foreach ($matches as $match) {
                $metric = $match[2];
                $team1_value = rtrim($match[3], '%');
                $team2_value = rtrim($match[4], '%');

                // Преобразуем значения в числа
                $team1_value = is_numeric($team1_value) ? (float) $team1_value : 0;
                $team2_value = is_numeric($team2_value) ? (float) $team2_value : 0;

                $result[$title][$metric] = [$team1_value, $team2_value];
            }

            // Добавляем нули для отсутствующих метрик
            $all_metrics = [
                "Владение мячом", "Удары", "Удары в створ", "Удары мимо", "Блок-но ударов",
                "Штрафные", "Угловые", "Офсайды", "Вбрасывания", "Сэйвы", "Фолы",
                "Желтые карточки", "Атаки", "Опасные атаки", "Красные карточки"
            ];

            foreach ($all_metrics as $metric) {
                if (!isset($result[$title][$metric])) {
                    $result[$title][$metric] = [0, 0];
                }
            }
        }

        return $result;
    }

    private static function insertStatistic(array $data, Game $game)
    {
        foreach ($data as $nameSectionGame => $dataSectionGame) {
            if ($nameSectionGame == 'Матч') {
                $typeSectionGame = 'FULL_GAME';
                $timeEvent = '90:00';
            } elseif ($nameSectionGame == '1-й тайм') {
                $typeSectionGame = 'ONE_PERIOD';
                $timeEvent = '45:00';
            } elseif ($nameSectionGame == '2-й тайм') {
                $typeSectionGame = 'TWO_PERIOD';
                $timeEvent = '90:00';
            } else {
                Log::error('Не нашли совпадение в Матч/1-й тайм/2-й тайм');
                return false;
            }

            foreach ($dataSectionGame as $typeEvent => $dataEvent) {
                if ($typeEvent == 'Владение мячом') {
                    $typeEvent = 'VLADENIE_MYACHEM';
                } elseif ($typeEvent == 'Удары') {
                    $typeEvent = 'UDARY';
                } elseif ($typeEvent == 'Удары в створ') {
                    $typeEvent = 'UDARY_V_STVOR';
                } elseif ($typeEvent == 'Удары мимо') {
                    $typeEvent = 'UDARY_MIMO';
                } elseif ($typeEvent == 'Блок-но ударов') {
                    $typeEvent = 'UDARY_BLOCK';
                } elseif ($typeEvent == 'Штрафные') {
                    $typeEvent = 'SHTRAFNYE';
                } elseif ($typeEvent == 'Угловые') {
                    $typeEvent = 'UGLOVYE';
                } elseif ($typeEvent == 'Офсайды') {
                    $typeEvent = 'OFFSAIDY';
                } elseif ($typeEvent == 'Сэйвы') {
                    $typeEvent = 'SEIVY';
                } elseif ($typeEvent == 'Фолы') {
                    $typeEvent = 'FOLY';
                } elseif ($typeEvent == 'Атаки') {
                    $typeEvent = 'ATAKI';
                } else {
                    $typeEvent = null;
                }



                if ($typeEvent != null) {
                    foreach ($dataEvent as $key => $value) {
                        Event::updateOrCreate(
                            [
                                'game_id'      => $game->id,
                                'type'         => $typeEvent,
                                'minute'       => $timeEvent,
                                'section_game' => $typeSectionGame,
                                'club_id'      => $key == 0 ? $game->club_home_id : $game->club_guest_id,
                            ],
                            [
                                'game_id'      => $game->id,
                                'type'         => $typeEvent,
                                'minute'       => $timeEvent,
                                'section_game' => $typeSectionGame,
                                'club_id'      => $key == 0 ? $game->club_home_id : $game->club_guest_id,
                                'value'        => $value,
                            ]
                        );
                    }
                }
            }
        }
    }
}
