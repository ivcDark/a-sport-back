<?php

namespace App\Service;

use App\Dto\ClubDto;
use App\Dto\CountryDto;
use App\Dto\PlayerDto;
use App\Models\ModelIntegration;
use App\Models\Player;
use App\Models\PlayerClub;
use Illuminate\Support\Facades\DB;

class PlayerService
{
    private string $typeIntegration;
    private string $nameModel;

    public function __construct($typeIntegration)
    {
        $this->typeIntegration = $typeIntegration;
        $this->nameModel = 'player';
    }

    public function create()
    {

    }

    public function updateOrCreate(PlayerDto $playerDto): ?\App\Models\Player
    {
        $player = null;

        try {
            DB::beginTransaction();

            $playerModelIntegration = ModelIntegration::where('integration_id', $playerDto->apiId)->where('model', $this->nameModel)->first();

            if ($playerModelIntegration == null) {
                $player = \App\Models\Player::create(
                    [
                        'fio' => $playerDto->fio,
                        'number' => $playerDto->number == '' ? null : $playerDto->number,
                        'fieldName' => $playerDto->fieldName,
                        'country_id' => $playerDto->countryId,
                        'slug' => $playerDto->slug,
                        'position' => $playerDto->position,
                        'image' => $playerDto->logo,
                    ]
                );

                ModelIntegration::create(
                    [
                        'model_id' => $player->id,
                        'model' => $this->nameModel,
                        'type_integration' => $this->typeIntegration,
                        'integration_id' => $playerDto->apiId
                    ]
                );

            } else {
                $player = Player::where('id', $playerModelIntegration->model_id)->first();
                $player->update([
                    'fio' => $playerDto->fio,
                    'fieldName' => $playerDto->fieldName,
                    'country_id' => $playerDto->countryId,
                    'slug' => $playerDto->slug,
                    'position' => $playerDto->position,
                    'image' => $playerDto->logo,
                    'birthday' => $playerDto->birthday != '' ? $playerDto->birthday : null,
                ]);
            }

            PlayerClub::updateOrCreate(
                [
                    'player_id' => $player->id,
                    'club_id' => $playerDto->clubId,
                ],
                [
                    'player_id' => $player->id,
                    'club_id' => $playerDto->clubId,
                ]
            );

            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
            dd($exception->getMessage(), $exception->getTraceAsString());
        }

        return $player;

    }
}
