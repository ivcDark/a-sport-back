<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClubResource;
use App\Service\ClubService;
use Illuminate\Http\Request;

class ClubController extends Controller
{
    public function get(Request $request)
    {
        $params = $request->all();
        $service = new ClubService();
        $clubs = $service->get($params);

        return ClubResource::collection($clubs)->additional(['status' => true]);
    }
}
