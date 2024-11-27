<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CalendarResourceTypeResource;
use App\Models\CalendarResourceType;

class CalendarResourceTypeController extends Controller
{
    public function index()
    {
        $resourceTypes = CalendarResourceType::all();

        return CalendarResourceTypeResource::collection($resourceTypes);
    }
}
