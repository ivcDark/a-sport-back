<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PlayerRequest;
use App\Http\Resources\PlayerResource;
use App\Service\PlayerService;

class PlayerController extends Controller
{
    public function get(PlayerRequest $request)
    {
        $data = $request->validated();
        return PlayerResource::collection((new PlayerService())->get($data))->additional(['status' => true]);
    }
}
