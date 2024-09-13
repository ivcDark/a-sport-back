<?php

namespace App\Service;

use App\Dto\ClubDto;
use App\Dto\CountryDto;
use App\Dto\GameDto;
use App\Dto\GameResultDto;
use App\Models\Club;
use App\Models\ClubLeague;
use App\Models\LeagueSeason;
use App\Models\ModelIntegration;
use App\Models\Season;
use Illuminate\Support\Facades\DB;

class GameResultService
{
    private ?string $typeIntegration;
    private string $nameModel;

    public function __construct($typeIntegration = null)
    {
        $this->typeIntegration = $typeIntegration;
        $this->nameModel = 'game_results';
    }

    public function create()
    {

    }

    public function updateOrCreate(GameResultDto $gameResultDto): ?\App\Models\GameResult
    {
        $gameResult = null;

        try {
            DB::beginTransaction();

            $gameResult = \App\Models\GameResult::updateOrCreate(
                [
                    'game_id'     => $gameResultDto->gameId,
                    'home_goals'  => $gameResultDto->homeGoals,
                    'guest_goals' => $gameResultDto->guestGoals,
                ],
                [
                    'game_id'     => $gameResultDto->gameId,
                    'home_goals'  => $gameResultDto->homeGoals,
                    'guest_goals' => $gameResultDto->guestGoals,
                ]
            );

            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
            dd($exception->getMessage());
        }

        return $gameResult;

    }
}
