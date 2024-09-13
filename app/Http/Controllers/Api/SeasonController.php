<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CountryResource;
use App\Http\Resources\LeagueResource;
use App\Http\Resources\SeasonResource;
use App\Models\Season;
use App\Service\CountryService;
use App\Service\LeagueService;
use Illuminate\Http\Request;

class SeasonController extends Controller
{
    public function get()
    {
        return SeasonResource::collection(Season::orderBy('title', 'desc')->get())->additional(['status' => true]);
    }
}
