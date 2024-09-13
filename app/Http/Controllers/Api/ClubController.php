<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClubResource;
use App\Http\Resources\CountryResource;
use App\Http\Resources\LeagueResource;
use App\Service\ClubService;
use App\Service\CountryService;
use App\Service\LeagueService;
use Illuminate\Http\Request;

class ClubController extends Controller
{
    public function get(Request $request)
    {
        $params = $request->all();
        $service = new ClubService();
        $leagues = $service->get($params);

        return ClubResource::collection($leagues)->additional(['status' => true]);
    }
}
