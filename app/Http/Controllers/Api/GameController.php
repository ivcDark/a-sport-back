<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GameRequest;
use App\Http\Resources\GameResource;
use App\Service\GameService;

class GameController extends Controller
{
    public function get(GameRequest $request)
    {
        $data = $request->validated();
        return GameResource::collection((new GameService())->get($data))->additional(['status' => true]);
    }
}
