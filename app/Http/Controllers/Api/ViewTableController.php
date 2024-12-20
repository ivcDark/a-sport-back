<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CountryResource;
use App\Http\Resources\LeagueResource;
use App\Http\Resources\SeasonResource;
use App\Http\Resources\ViewTableBestClubSeasonResource;
use App\Http\Resources\ViewTableBestPlayerOnPositionResource;
use App\Http\Resources\ViewTableBestPlayerSeasonResource;
use App\Http\Resources\ViewTableGoalsPlayerToClubResource;
use App\Http\Resources\ViewTableTopGameResource;
use App\Models\Season;
use App\Service\CountryService;
use App\Service\LeagueService;
use App\Service\ViewTableBestClubSeasonService;
use App\Service\ViewTableBestPlayerOnPositionService;
use App\Service\ViewTableBestPlayerSeasonService;
use App\Service\ViewTableService;
use App\Service\ViewTableTopGameService;
use Illuminate\Http\Request;

class ViewTableController extends Controller
{
    /**
     * Главная страница. Лучшие клубы сезона по показателям.
     */
    public function bestClubSeason(Request $request, ViewTableBestClubSeasonService $service)
    {
        $result = $service->get($request->all());
        return ViewTableBestClubSeasonResource::collection($result)->additional(['status' => true]);
    }

    /**
     * Главная страница. Топовый матч.
     */
    public function topGame()
    {
        $result = (new ViewTableTopGameService())->get();
        return ViewTableTopGameResource::collection($result)->additional(['status' => true]);
    }

    /**
     * Главная страница. Лучшие футболисты сезона по показателям.
     */
    public function bestPlayerSeason(Request $request)
    {
        $params = $request->all();
        $service = new ViewTableBestPlayerSeasonService();
        $result = $service->get($params);

        return ViewTableBestPlayerSeasonResource::collection($result)->additional(['status' => true]);
    }

    public function goalsPlayerToClub(ViewTableService $service)
    {
        return ViewTableGoalsPlayerToClubResource::collection($service->get())->additional(['status' => true]);
    }

    public function betsPlayersOnPosition(ViewTableBestPlayerOnPositionService $service)
    {
        return ViewTableBestPlayerOnPositionResource::collection($service->get()->sortBy('position'))->additional(['status' => true]);
    }
}
