<?php

namespace App\Console\Commands\Parse\ApiFootball;

use App\Dto\ClubDto;
use App\Dto\GameDto;
use App\Dto\GamePlayerDto;
use App\Dto\GameResultDto;
use App\Dto\PlayerDto;
use App\Models\LeagueSeason;
use App\Models\ModelIntegration;
use App\Models\Season;
use App\Service\GamePlayerService;
use App\Service\GameResultService;
use App\Service\GameService;
use Illuminate\Console\Command;
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
            'from' => '2024-01-01',
            'to'   => '2024-09-31'
        ];
        $leagueModel = \App\Models\League::where('id', '9cc1a42e-4363-4f4d-babb-5881ed14a528')->first(); // РФПЛ

        $this->info("Матчи с: {$dates['from']} по {$dates['to']}, лига: {$leagueModel->name}");

        $parse = new \App\Parse\ApiFootball\Game(0, $dates);
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
                            }
                        }
                    }
                }
            }
        }

        return true;
    }
}
