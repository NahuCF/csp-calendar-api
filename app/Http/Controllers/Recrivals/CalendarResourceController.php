<?php

namespace App\Http\Controllers\Recrivals;

use App\Http\Controllers\Controller;
use App\Http\Resources\CalendarResourceResource;
use App\Models\CalendarResource;
use Illuminate\Http\Request;

class CalendarResourceController extends Controller
{
    public function index(Request $request)
    {
        $input = $request->validate([
            'search' => ['sometimes'],
            'identifier' => ['required'],
        ]);

        $search = data_get($input, 'search');
        $identifier = data_get($input, 'identifier');

        $resources = CalendarResource::query()
            ->withCount('events')
            ->with('user', 'facility', 'calendarResourceType')
            ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->whereHas('user.tenant', fn ($q) => $q->where('identifier', $identifier))
            ->orderBy('id', 'asc')
            ->paginate(15);

        return CalendarResourceResource::collection($resources);
    }
}
