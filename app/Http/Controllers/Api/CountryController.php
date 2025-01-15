<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CountryResource;
use App\Models\CalendarEvent;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CountryController extends Controller
{
    public function index(Request $request)
    {
        $countries = Country::query()
            ->with('subdivisions')
            ->get();

        return CountryResource::collection($countries);
    }

    public function countriesWithEvents()
    {
        $user = Auth::user();

        $calendarEvents = CalendarEvent::query()
            ->select('facilities.country_id', 'facilities.country_subdivision_id')
            ->join('calendar_resources', 'calendar_events.calendar_resource_id', '=', 'calendar_resources.id')
            ->join('facilities', 'calendar_resources.facility_id', '=', 'facilities.id')
            ->where('calendar_events.tenant_id', $user->tenant_id)
            ->get();

        $countryIds = $calendarEvents
            ->pluck('country_id')
            ->unique();

        $countrySubdivisionIds = $calendarEvents
            ->pluck('country_subdivision_id')
            ->unique();

        $countries = Country::query()
            ->with(['subdivisions' => function ($query) use ($countrySubdivisionIds) {
                $query->whereIn('id', $countrySubdivisionIds);
            }])
            ->whereIn('id', $countryIds)
            ->get();

        return CountryResource::collection($countries);
    }
}
