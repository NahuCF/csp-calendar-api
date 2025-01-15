<?php

namespace App\Http\Controllers\Recrivals;

use App\Http\Controllers\Controller;
use App\Http\Resources\CalendarEventResource;
use App\Models\CalendarEvent;
use Illuminate\Http\Request;

class CalendarEventController extends Controller
{
    public function index(Request $request)
    {
        $input = $request->validate([
            'calendar_resource_type_id' => ['sometimes', 'integer'],
            'facility_ids' => ['sometimes', 'array'],
            'is_paid' => ['sometimes'],
            'country_subdivision_id' => ['sometimes', 'integer'],
            'identifier' => ['required'],
        ]);

        $calendarResourceId = data_get($input, 'calendar_resource_type_id');
        $facilityIds = data_get($input, 'facility_ids', []);
        $isPaid = data_get($input, 'is_paid');
        $isPaidBoolean = $isPaid == 'true' ? true : false;
        $countrySubdivisionId = data_get($input, 'country_subdivision_id');
        $identifier = data_get($input, 'identifier');

        $calendarEvents = CalendarEvent::query()
            ->with(['resource.facility', 'user'])
            ->when($isPaid, fn ($q) => $q->where('is_paid', $isPaidBoolean))
            ->when(! empty($facilityIds), fn ($q) => $q->whereHas('resource.facility', fn ($query) => $query->whereIn('id', $facilityIds)))
            ->when($countrySubdivisionId, fn ($q) => $q->whereHas('resource.facility', fn ($query) => $query->where('country_subdivision_id', $countrySubdivisionId)))
            ->when($calendarResourceId, fn ($q) => $q->where('calendar_resource_id', $calendarResourceId))
            ->whereHas('user.tenant', fn ($q) => $q->where('identifier', $identifier))
            ->get();

        return CalendarEventResource::collection($calendarEvents);
    }
}
