<?php

namespace App\Service;

use App\Dto\ClubDto;
use App\Dto\CountryDto;
use App\Dto\GamePlayerDto;
use App\Dto\PlayerDto;
use App\Models\GamePlayer;
use App\Models\ModelIntegration;
use App\Models\Player;
use App\Models\PlayerClub;
use Illuminate\Support\Facades\DB;

class GamePlayerService
{
    private string $typeIntegration;
    private string $nameModel;

    public function __construct($typeIntegration)
    {
        $this->typeIntegration = $typeIntegration;
        $this->nameModel = 'game_players';
    }

    public function create()
    {

    }

    public function updateOrCreate(GamePlayerDto $gamePlayerDto): ?\App\Models\GamePlayer
    {
        $gamePlayer = null;

        try {
            DB::beginTransaction();

            $gamePlayer = GamePlayer::updateOrCreate(
                [
                    'game_id'          => $gamePlayerDto->gameId,
                    'player_id'        => $gamePlayerDto->playerId,
                    'club_id'          => $gamePlayerDto->clubId,
                    'is_start_group'   => $gamePlayerDto->isStartGroup,
                    'is_reserve_group' => $gamePlayerDto->isReserveGroup,
                    'is_injured_group' => $gamePlayerDto->isInjuredGroup,
                    'is_best'          => $gamePlayerDto->isBest,
                    'rating'           => $gamePlayerDto->rating,
                ],
                [
                    'game_id'          => $gamePlayerDto->gameId,
                    'player_id'        => $gamePlayerDto->playerId,
                    'club_id'          => $gamePlayerDto->clubId,
                    'is_start_group'   => $gamePlayerDto->isStartGroup,
                    'is_reserve_group' => $gamePlayerDto->isReserveGroup,
                    'is_injured_group' => $gamePlayerDto->isInjuredGroup,
                    'is_best'          => $gamePlayerDto->isBest,
                    'rating'           => $gamePlayerDto->rating,
                ]
            );

            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
            dd($exception->getMessage(), $exception->getTraceAsString());
        }

        return $gamePlayer;

    }
}
