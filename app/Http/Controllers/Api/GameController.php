<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\GameResource;
use App\Models\Game;
use Illuminate\Http\Request;

class GameController extends Controller
{
    public function get(Request $request)
    {
        return GameResource::collection(Game::orderBy('time_start', 'desc')->get())->additional(['status' => true]);
    }
}
