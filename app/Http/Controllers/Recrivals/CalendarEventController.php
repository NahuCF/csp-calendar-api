<?php

namespace App\Http\Controllers\Recrivals;

use App\Http\Controllers\Controller;
use App\Http\Resources\CalendarEventResource;
use App\Models\CalendarEvent;
use App\Models\Tenant;
use Illuminate\Http\Request;

class CalendarEventController extends Controller
{
    public function index(Request $request)
    {
        $input = $request->validate([
            'identifier' => ['required'],
        ]);

        $identifier = data_get($input, 'identifier');

        $tenant = Tenant::query()
            ->where('identifier', $identifier)
            ->first();

        $calendarEvents = CalendarEvent::query()
            ->with(['resource.facility', 'user'])
            ->where('tenant_id', $tenant->id)
            ->where('rejected', false)
            ->get();

        return CalendarEventResource::collection($calendarEvents);
    }
}
