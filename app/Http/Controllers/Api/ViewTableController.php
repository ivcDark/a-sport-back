<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CountryResource;
use App\Http\Resources\LeagueResource;
use App\Http\Resources\SeasonResource;
use App\Http\Resources\ViewTableBestClubSeasonResource;
use App\Http\Resources\ViewTableTopGameResource;
use App\Models\Season;
use App\Service\CountryService;
use App\Service\LeagueService;
use App\Service\ViewTableBestClubSeasonService;
use App\Service\ViewTableTopGameService;
use Illuminate\Http\Request;

class ViewTableController extends Controller
{
    public function bestClubSeason(Request $request)
    {
        $params = $request->all();
        $service = new ViewTableBestClubSeasonService();
        $result = $service->get($params);

        return ViewTableBestClubSeasonResource::collection($result)->additional(['status' => true]);
    }

    public function topGame()
    {
        $result = (new ViewTableTopGameService())->get();
        return ViewTableTopGameResource::collection($result)->additional(['status' => true]);
    }
}
