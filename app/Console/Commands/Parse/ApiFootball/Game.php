<?php

namespace App\Console\Commands\Parse\ApiFootball;

use App\Dto\ClubDto;
use App\Dto\EventDto;
use App\Dto\GameDto;
use App\Dto\GamePlayerDto;
use App\Dto\GameResultDto;
use App\Dto\GameStatisticDto;
use App\Dto\PlayerDto;
use App\Models\LeagueSeason;
use App\Models\ModelIntegration;
use App\Models\Season;
use App\Service\EventService;
use App\Service\GamePlayerService;
use App\Service\GameResultService;
use App\Service\GameService;
use App\Service\GameStatisticService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class Game extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api_football:game';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Парсинг api_football - матчи';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dates = [
            'from' => '2024-07-01',
            'to'   => '2024-10-31'
        ];
        $leagueModel = \App\Models\League::where('id', '9cc1a42e-4363-4f4d-babb-5881ed14a528')->first(); // РФПЛ

        $this->info("Матчи с: {$dates['from']} по {$dates['to']}, лига: {$leagueModel->name}");

        $parse = new \App\Parse\ApiFootball\Game($leagueModel->apiFootballId, $dates);
        $games = $parse->start()->toArray();

        if (isset($games['error'])) {
            $this->error($games['message']);
            return false;
        }

        foreach ($games as $item) {
            $this->info('Матч: ' . $item['match_id']);

            $modelIntegrationToLeague = ModelIntegration::where('model', 'league')
                ->where('type_integration', 'apiFootball')
                ->where('integration_id', $item['league_id'])
                ->first();

            if ($modelIntegrationToLeague == null) {
                $this->error($item['league_name'] . ' не найдено');
                return false;
            }

            $leagueModel = \App\Models\League::where('id', $modelIntegrationToLeague->model_id)->first();
            $seasonModel = Season::where('title', $item['league_year'])->first();
            $leagueSeasonModel = LeagueSeason::where('season_id', $seasonModel->id)->where('league_id', $leagueModel->id)->first();
            $modelIntegration = ModelIntegration::where('model', 'club')->where('type_integration', 'apiFootball')->where('integration_id', $item['match_hometeam_id'])->first();
            $clubHomeModel = \App\Models\Club::where('id', $modelIntegration->model_id)->first();
            $modelIntegration = ModelIntegration::where('model', 'club')->where('type_integration', 'apiFootball')->where('integration_id', $item['match_awayteam_id'])->first();
            $clubAwayModel = \App\Models\Club::where('id', $modelIntegration->model_id)->first();
            $timeStart = strtotime($item['match_date'] . " " . $item['match_time']);

            if ($leagueSeasonModel == null) {
                $this->warn('Нет league_season в базе (Сезон: ' . $seasonModel->title . '; Лига: ' . $leagueModel->name . ' ' . $leagueModel->id);
                continue;
            }

            $dataToGameDto = [
                'clubHomeId'     => $clubHomeModel->id,
                'clubGuestId'    => $clubAwayModel->id,
                'leagueSeasonId' => $leagueSeasonModel->id,
                'tour'           => $item['match_round'],
                'timeStart'      => $timeStart,
                'apiId'          => $item['match_id'],
            ];

            $dtoGame = new GameDto($dataToGameDto);
            $gameModel = (new GameService('apiFootball'))->updateOrCreate($dtoGame);

            if ($gameModel != null) {
                $this->info('Создали матч ' . $gameModel->id);
            }

            if ($item['match_status'] == 'Finished') {
                $dataToGameResultDto = [
                    'gameId' => $gameModel->id,
                    'homeGoals' => $item['match_hometeam_score'],
                    'guestGoals' => $item['match_awayteam_score'],
                ];

                $dtoGameResult = new GameResultDto($dataToGameResultDto);
                $gameResultModel = (new GameResultService('apiFootball'))->updateOrCreate($dtoGameResult);

                if ($gameResultModel != null) {
                    $this->info('Создали результат матча ' . $gameResultModel->id);
                }

                foreach ($item['lineup'] as $typeTeam => $groupsTeam) {
                    foreach ($groupsTeam as $typeGroup => $groupPlayers) {
                        foreach ($groupPlayers as $infoPlayer) {
                            if ($typeGroup == 'starting_lineups' || $typeGroup == 'substitutes') {
                                $modelIntegration = ModelIntegration::where('model', 'player')
                                    ->where('type_integration', 'apiFootball')
                                    ->where('integration_id', $infoPlayer['player_key'])
                                    ->first();

                                if ($modelIntegration != null) {
                                    $dataToGamePlayerDto = [
                                        'gameId'         => $gameModel->id,
                                        'playerId'       => $modelIntegration->model_id,
                                        'clubId'         => $typeTeam == 'home' ? $clubHomeModel->id : $clubAwayModel->id,
                                        'isStartGroup'   => $typeGroup == 'starting_lineups',
                                        'isReserveGroup' => $typeGroup == 'substitutes',
                                        'isInjuredGroup' => false,
                                        'isBest'         => false,
                                        'rating'         => null,
                                    ];

                                    $dtoGamePlayer = new GamePlayerDto($dataToGamePlayerDto);
                                    $gamePlayerModel = (new GamePlayerService('apiFootball'))->updateOrCreate($dtoGamePlayer);

                                    if ($gamePlayerModel != null) {
                                        $this->info('Создали игрока в матче (GamePlayer) ' . $gamePlayerModel->id);
                                    }
                                } else {
                                    $this->error("Игрока нет в базе. Player_key: {$infoPlayer['player_key']} FIO: {$infoPlayer['lineup_player']}");
                                }


                            }
                        }
                    }
                }

                if (count($item['statistics']) > 0) {
                    $this->info('Записываем статистику матча ' . $gameModel->id);
                    foreach ($item['statistics'] as $statistic) {
                        preg_match('/\d+/', $statistic['home'], $valueHome);
                        preg_match('/\d+/', $statistic['away'], $valueAway);
                        $dataToGameStatisticDto = [
                            'gameId' => $gameModel->id,
                            'clubId' => $clubHomeModel->id,
                            'sectionGame' => 'full_game',
                            'typeIndicator' => GameStatisticService::getTypeIndicator($statistic['type']),
                            'value' => $valueHome[0],
                        ];
                        $dtoGameStatistic = new GameStatisticDto($dataToGameStatisticDto);
                        $gameStatisticModel = (new GameStatisticService('apiFootball'))->updateOrCreate($dtoGameStatistic);

                        $dataToGameStatisticDto = [
                            'gameId' => $gameModel->id,
                            'clubId' => $clubAwayModel->id,
                            'sectionGame' => 'full_game',
                            'typeIndicator' => GameStatisticService::getTypeIndicator($statistic['type']),
                            'value' => $valueAway[0],
                        ];
                        $dtoGameStatistic = new GameStatisticDto($dataToGameStatisticDto);
                        $gameStatisticModel = (new GameStatisticService('apiFootball'))->updateOrCreate($dtoGameStatistic);
                    }
                }

                if (count($item['statistics_1half']) > 0) {
                    $this->info('Записываем статистику 1 тайма в матче ' . $gameModel->id);
                    foreach ($item['statistics_1half'] as $statistic) {
                        preg_match('/\d+/', $statistic['home'], $valueHome);
                        preg_match('/\d+/', $statistic['away'], $valueAway);
                        $dataToGameStatisticDto = [
                            'gameId' => $gameModel->id,
                            'clubId' => $clubHomeModel->id,
                            'sectionGame' => '1half',
                            'typeIndicator' => GameStatisticService::getTypeIndicator($statistic['type']),
                            'value' => $valueHome[0],
                        ];
                        $dtoGameStatistic = new GameStatisticDto($dataToGameStatisticDto);
                        $gameStatisticModel = (new GameStatisticService('apiFootball'))->updateOrCreate($dtoGameStatistic);

                        $dataToGameStatisticDto = [
                            'gameId' => $gameModel->id,
                            'clubId' => $clubAwayModel->id,
                            'sectionGame' => '1half',
                            'typeIndicator' => GameStatisticService::getTypeIndicator($statistic['type']),
                            'value' => $valueAway[0],
                        ];
                        $dtoGameStatistic = new GameStatisticDto($dataToGameStatisticDto);
                        $gameStatisticModel = (new GameStatisticService('apiFootball'))->updateOrCreate($dtoGameStatistic);
                    }
                }

                if (count($item['cards']) > 0) {
                    $this->info('Записываем желтые карточки матча ' . $gameModel->id);
                    foreach ($item['cards'] as $card) {
                        if ($card['home_player_id'] == "" && $card['away_player_id'] == "") {
                            $fioPlayer = $card['home_fault'] != "" ? $card['home_fault'] : $card['away_fault'];
                            Log::channel('apiFootballEvents')
                                ->info('EVENT :: Нет ID игрока, который получил карточку. Проверьте вручную.');
                            Log::channel('apiFootballEvents')
                                ->info('GameId: ' . $gameModel->id . ' ФИО забитого игрока: ' . $fioPlayer . ' Время: ' . $card['time']);
                            continue;
                        }

                        $this->info('Данные по карточке: ' . json_encode($card));
                        $sectionGame = 'no';
                        if ($card['score_info_time'] == '1st Half') {
                            $sectionGame = '1half';
                        }
                        if ($card['score_info_time'] == '2nd Half') {
                            $sectionGame = '2half';
                        }

                        if ($sectionGame == 'no') {
                            Log::channel('apiFootballEvents')
                                ->info('EVENT :: Не смогли определить 1 или 2 тайм (КАРТОЧКА). Ключ score_info_time = ' . $card['score_info_time']);
                        }

                        $player = $card['home_player_id'] != ""
                            ? ModelIntegration::where('integration_id', $card['home_player_id'])->where('type_integration', 'apiFootball')->first()
                            : ModelIntegration::where('integration_id', $card['away_player_id'])->where('type_integration', 'apiFootball')->first();

                        $dataToEventDto = [
                            'gameId'      => $gameModel->id,
                            'clubId'      => $card['home_player_id'] != "" ? $clubHomeModel->id : $clubAwayModel->id,
                            'playerId'    => $player->model_id,
                            'type'        => EventService::getTypeIndicator($card['card']),
                            'minute'      => $card['time'],
                            'sectionGame' => $sectionGame,
                            'value'       => 1,
                        ];
                        $dtoEvent = new EventDto($dataToEventDto);
                        $eventModel = (new EventService('apiFootball'))->updateOrCreate($dtoEvent);
                    }
                }

                if (count($item['goalscorer']) > 0) {
                    $this->info('Записываем голы и голевые передачи матча ' . $gameModel->id);
                    foreach ($item['goalscorer'] as $goalInfo) {
                        if ($goalInfo['home_scorer_id'] == "" && $goalInfo['away_scorer_id'] == "") {
                            $fioPlayer = $goalInfo['home_scorer'] != "" ? $goalInfo['home_scorer'] : $goalInfo['away_scorer'];
                            Log::channel('apiFootballEvents')
                                ->info('EVENT :: Нет ID игрока, который забил гол. Проверьте вручную автора гола.');
                            Log::channel('apiFootballEvents')
                                ->info('GameId: ' . $gameModel->id . ' ФИО забитого игрока: ' . $fioPlayer . ' Время: ' . $goalInfo['time']);
                            continue;
                        }

                        $sectionGame = 'no';
                        if ($goalInfo['score_info_time'] == '1st Half') {
                            $sectionGame = '1half';
                        }
                        if ($goalInfo['score_info_time'] == '2nd Half') {
                            $sectionGame = '2half';
                        }

                        if ($sectionGame == 'no') {
                            Log::channel('apiFootballEvents')
                                ->info('EVENT :: Не смогли определить 1 или 2 тайм (ГОЛ). Ключ score_info_time = ' . $goalInfo['score_info_time']);
                        }

                        $player = $goalInfo['home_scorer_id'] != ""
                            ? ModelIntegration::where('integration_id', $goalInfo['home_scorer_id'])->where('type_integration', 'apiFootball')->first()
                            : ModelIntegration::where('integration_id', $goalInfo['away_scorer_id'])->where('type_integration', 'apiFootball')->first();

                        $dataToEventDto = [
                            'gameId'      => $gameModel->id,
                            'clubId'      => $goalInfo['home_scorer_id'] != "" ? $clubHomeModel->id : $clubAwayModel->id,
                            'playerId'    => $player->model_id,
                            'type'        => EventService::getTypeIndicator('goal'),
                            'minute'      => $goalInfo['time'],
                            'sectionGame' => $sectionGame,
                            'value'       => 1,
                        ];
                        $dtoEvent = new EventDto($dataToEventDto);
                        $eventModel = (new EventService('apiFootball'))->updateOrCreate($dtoEvent);

                        if ($goalInfo['home_assist_id'] != "" && $goalInfo['away_assist_id'] != "") {
                            $sectionGame = 'no';
                            if ($goalInfo['score_info_time'] == '1st Half') {
                                $sectionGame = '1half';
                            }
                            if ($goalInfo['score_info_time'] == '2nd Half') {
                                $sectionGame = '2half';
                            }

                            if ($sectionGame == 'no') {
                                Log::channel('apiFootballEvents')
                                    ->info('EVENT :: Не смогли определить 1 или 2 тайм (ГОЛЕВАЯ ПЕРЕДАЧА). Ключ score_info_time = ' . $goalInfo['score_info_time']);
                            }

                            $player = $goalInfo['home_assist_id'] != ""
                                ? ModelIntegration::where('integration_id', $goalInfo['home_assist_id'])->where('type_integration', 'apiFootball')->first()
                                : ModelIntegration::where('integration_id', $goalInfo['away_assist_id'])->where('type_integration', 'apiFootball')->first();

                            $dataToEventDto = [
                                'gameId'      => $gameModel->id,
                                'clubId'      => $goalInfo['home_assist_id'] != "" ? $clubHomeModel->id : $clubAwayModel->id,
                                'playerId'    => $player->model_id,
                                'type'        => EventService::getTypeIndicator('assist'),
                                'minute'      => $goalInfo['time'],
                                'sectionGame' => $sectionGame,
                                'value'       => 1,
                            ];
                            $dtoEvent = new EventDto($dataToEventDto);
                            $eventModel = (new EventService('apiFootball'))->updateOrCreate($dtoEvent);
                        }
                    }
                }
            }
        }

        return true;
    }
}
