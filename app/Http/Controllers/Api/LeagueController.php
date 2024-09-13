<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CountryResource;
use App\Http\Resources\LeagueResource;
use App\Service\CountryService;
use App\Service\LeagueService;
use Illuminate\Http\Request;

class LeagueController extends Controller
{
    public function get(Request $request)
    {
        $params = $request->all();
        $service = new LeagueService();
        $leagues = $service->get($params);

        return LeagueResource::collection($leagues)->additional(['status' => true]);
    }
}
