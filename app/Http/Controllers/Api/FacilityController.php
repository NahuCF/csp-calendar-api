<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FacilityResource;
use App\Models\CalendarEvent;
use App\Models\CalendarResourceType;
use App\Models\CountrySubdivision;
use App\Models\Facility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FacilityController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $facilities = Facility::query()
            ->where('tenant_id', $user->tenant_id)
            ->get();

        $resourceTypes = CalendarResourceType::query()
            ->get();

        return FacilityResource::collection($facilities)->additional([
            'meta' => [
                'resource_types' => $resourceTypes,
            ],
        ]);
    }

    public function facilitiesWithEvents()
    {
        $user = Auth::user();

        $resourceIds = CalendarEvent::query()
            ->select('calendar_resource_id')
            ->where('tenant_id', $user->tenant_id)
            ->pluck('calendar_resource_id')
            ->unique();

        $uniqueFacilities = Facility::query()
            ->whereIn('id', $resourceIds)
            ->get();

        return FacilityResource::collection($uniqueFacilities);
    }

    public function store(Request $request)
    {
        $input = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'country_subdivision_id' => ['required', 'integer'],
        ]);

        $user = Auth::user();

        $name = data_get($input, 'name');
        $countrySubdivisionId = data_get($input, 'country_subdivision_id');

        $countryId = CountrySubdivision::query()
            ->firstWhere('id', $countrySubdivisionId)
            ->country_id;

        $facility = Facility::query()
            ->create([
                'name' => $name,
                'user_id' => $user->id,
                'tenant_id' => $user->tenant_id,
                'country_id' => $countryId,
                'country_subdivision_id' => $countrySubdivisionId,
            ]);

        return FacilityResource::make($facility);
    }
}
