<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CountryResource;
use App\Service\CountryService;
use Illuminate\Http\Request;

class CountryController extends Controller
{
    public function get(Request $request)
    {
        $params = $request->all();
        $service = new CountryService();
        $countries = $service->get($params);

        return CountryResource::collection($countries)->additional(['status' => true]);
    }
}
