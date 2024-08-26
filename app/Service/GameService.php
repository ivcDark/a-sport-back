<?php

namespace App\Service;

use App\Dto\ClubDto;
use App\Dto\CountryDto;
use App\Dto\GameDto;
use App\Models\Club;
use App\Models\ClubLeague;
use App\Models\LeagueSeason;
use App\Models\ModelIntegration;
use App\Models\Season;
use Illuminate\Support\Facades\DB;

class GameService
{
    private ?string $typeIntegration;
    private string $nameModel;

    public function __construct($typeIntegration = null)
    {
        $this->typeIntegration = $typeIntegration;
        $this->nameModel = 'games';
    }

    public function create()
    {

    }

    public function updateOrCreate(GameDto $gameDto): ?\App\Models\Game
    {
        $game = null;

        try {
            DB::beginTransaction();

            $game = \App\Models\Game::updateOrCreate(
                [
                    'club_home_id'     => $gameDto->clubHomeId,
                    'club_guest_id'    => $gameDto->clubGuestId,
                    'league_season_id' => $gameDto->leagueSeasonId,
                    'tour'             => $gameDto->tour,
                    'time_start'       => $gameDto->timeStart,
                ],
                [
                    'club_home_id'     => $gameDto->clubHomeId,
                    'club_guest_id'    => $gameDto->clubGuestId,
                    'league_season_id' => $gameDto->leagueSeasonId,
                    'tour'             => $gameDto->tour,
                    'time_start'       => $gameDto->timeStart,
                ]
            );

            ModelIntegration::firstOrCreate(
                [
                    'model_id'         => $game->id,
                    'model'            => $this->nameModel,
                    'type_integration' => $this->typeIntegration,
                    'integration_id'   => $gameDto->apiId
                ],
                [
                    'model_id'         => $game->id,
                    'model'            => $this->nameModel,
                    'type_integration' => $this->typeIntegration,
                    'integration_id'   => $gameDto->apiId
                ]
            );

            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
            dd($exception->getMessage());
        }

        return $game;

    }
}
