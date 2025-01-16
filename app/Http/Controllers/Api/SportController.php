<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SportResource;
use App\Models\Sport;

class SportController extends Controller
{
    public function index()
    {
        $sports = Sport::query()
            ->orderBy('name')
            ->get();

        return SportResource::collection($sports);
    }
}
