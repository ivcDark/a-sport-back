<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\FilterTableRequest;
use App\Http\Resources\FilterTableResource;
use App\Service\FilterTableService;
use Illuminate\Http\Request;

class FilterController extends Controller
{
    public function get(FilterTableRequest $request)
    {
        $params = $request->validated();
        $service = new FilterTableService();
        $data = $service->get($params);

        return FilterTableResource::collection($data)->additional(['status' => true]);
    }
}
