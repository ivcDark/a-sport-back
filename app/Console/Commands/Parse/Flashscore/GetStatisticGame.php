<?php

namespace App\Console\Commands\Parse\Flashscore;

use App\Models\Game;
use App\Models\GameStatistic;
use App\Models\StatType;
use App\Models\StatTypeMapping;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class GetStatisticGame extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'flashscore:get-game-statistic {--league_season= : ID лиги сезона}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'FlashScore - получение статистики матчей в сезоне';

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

                $this->info("Формируем список игр и их статистику");

                $statistics = $this->getGameStatistics($games);

                $this->info("Статистика по матчам готова. Приступаем к insert в БД");

                if ($this->insertStatistics()) {
                    $this->info('Загрузка статистики игр завершена');
                    return 1;
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

    private function getGameStatistics($games): array
    {
        $statistics = [];
        foreach ($games as $game) {
            $this->info("Отправляем запрос на получение статистики по матчу {$game->flashscore_id}");

            $result = Http::withHeaders([
                    'x-fsign' => 'SW9D1eZo'
                ])
                ->timeout(30)
                ->retry(3, 3000)
                ->get("https://2.flashscore.ninja/2/x/feed/df_st_1_{$game->flashscore_id}");

            if ($result->status() == 200) {
                $sections = explode('SE÷', $result->body());
                $statistic = [];

                foreach ($sections as $section) {
                    if (empty($section)) continue;

                    [$periodRaw, $rest] = explode('¬~', $section, 2);
                    $period = trim($periodRaw);

                    // Разделяем по категориям (начинаются с SF÷)
                    preg_match_all('/SF÷(.*?)¬~(.*?)(?=(SF÷|$))/s', $rest, $categories, PREG_SET_ORDER);

                    foreach ($categories as $cat) {
                        $category = trim($cat[1]);
                        $block = $cat[2];

                        // Ищем блоки с данными SD÷, SG÷, SH÷, SI÷
                        preg_match_all('/SD÷(\d+)¬SG÷(.*?)¬SH÷(.*?)¬SI÷(.*?)¬~/s', $block, $stats, PREG_SET_ORDER);

                        foreach ($stats as $stat) {
                            $statistic[] = [
                                'game_id' => $game->id,
                                'club_home_id' => $game->club_home_id,
                                'club_guest_id' => $game->club_guest_id,
                                'period' => $period,
                                'category' => $category,
                                'stat_id' => (int)$stat[1],
                                'stat_type_name' => $stat[2],
                                'club_value_1' => trim($stat[3]),
                                'club_value_2' => trim($stat[4]),
                            ];
                        }
                    }
                }

                $statistics[$game->flashscore_id] = $statistic;
            } else {
                $this->error("Произошла ошибка во время отправление запроса");
                $this->error("Message: " . $result->body());
                throw new \Exception("Произошла ошибка во время отправление запроса");
            }
        }
        file_put_contents('statistics.json', json_encode($statistics, JSON_UNESCAPED_UNICODE));
        $this->info("Записали всю информацию в файл");
        return $statistics;
    }


    private function insertStatistics(): bool
    {
        $data = json_decode(file_get_contents('statistics.json'), true);
        foreach ($data as $game) {
            foreach ($game as $item) {
                $statType = StatType::where('name', $item['stat_type_name'])->where('category',$item['category'])->first();
                if ($statType == null) {
                    $statType = StatType::create([
                        'name' => $item['stat_type_name'],
                        'category' => $item['category'],
                    ]);
                    $this->warn("Не было параметра '{$statType->name}'. Мы его создали в таблице stat_types");
                }
                $statTypeMapping = StatTypeMapping::where('source', 'flashscore')
                    ->where('original_id', $item['stat_id'])
                    ->where('original_name', $item['stat_type_name'])
                    ->where('stat_type_id', $statType->id)
                    ->first();
                if ($statTypeMapping == null) {
                    $statTypeMapping = StatTypeMapping::create([
                        'source' => 'flashscore',
                        'original_id' => $item['stat_id'],
                        'original_name' => $item['stat_type_name'],
                        'stat_type_id' => $statType->id,
                    ]);
                    $this->warn("Не было параметра '{$statTypeMapping->original_name}'. Мы его создали в таблице stat_type_mapping");
                }

                $period = match($item['period']) {
                    'Match' => 'full_game',
                    '1st Half' => '1_half',
                    '2nd Half' => '2_half',
                    default => 'unknown',
                };

                if ($period == 'unknown') {
                    throw new \Exception("Не смогли понять что за период. В массиве записано = {$data['period']}. Обработайте это, а потом запустите команду повторно.");
                }

                $gameStatisticHome = GameStatistic::updateOrCreate(
                    [
                        'game_id' => $item['game_id'],
                        'club_id' => $item['club_home_id'],
                        'period' => $period,
                        'stat_type_id' => $statType->id,
                    ],
                    [
                        'game_id' => $item['game_id'],
                        'club_id' => $item['club_home_id'],
                        'period' => $period,
                        'stat_type_id' => $statType->id,
                        'value' => $item['club_value_1'],
                    ]
                );

                $gameStatisticGuest = GameStatistic::updateOrCreate(
                    [
                        'game_id' => $item['game_id'],
                        'club_id' => $item['club_guest_id'],
                        'period' => $period,
                        'stat_type_id' => $statType->id,
                    ],
                    [
                        'game_id' => $item['game_id'],
                        'club_id' => $item['club_guest_id'],
                        'period' => $period,
                        'stat_type_id' => $statType->id,
                        'value' => $item['club_value_2'],
                    ]
                );

                $this->info("Записали статистику по игре {$item['game_id']}");
            }

        }

        $this->info("Все игры записали");
        return true;
    }

}
