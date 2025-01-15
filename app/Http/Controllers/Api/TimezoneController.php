<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TimezoneResource;
use App\Models\Timezone;
use Illuminate\Support\Facades\Cache;

class TimezoneController extends Controller
{
    public function index()
    {
        $timezones = Cache::remember(
            'timezones',
            60,
            fn () => Timezone::where('show', true)->get()
        );

        return TimezoneResource::collection($timezones);
    }
}
