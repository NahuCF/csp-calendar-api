<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FacilityResource;
use App\Models\CalendarEvent;
use App\Models\CalendarResource;
use App\Models\CalendarResourceType;
use App\Models\CountrySubdivision;
use App\Models\Facility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class FacilityController extends Controller
{
    public function index(Request $request)
    {
        $input = $request->validate([
            'paginated' => ['sometimes'],
            'search' => ['sometimes'],
        ]);

        $paginated = data_get($input, 'paginated', false);
        $search = data_get($input, 'search');

        $user = Auth::user();

        $facilities = Facility::query()
            ->with('user', 'country')
            ->withCount('resources')
            ->where('tenant_id', $user->tenant_id)
            ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('id', 'asc');

        $facilities = $paginated ? $facilities->paginate(15) : $facilities->get();

        $resourceTypes = CalendarResourceType::query()->get();

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

    public function update(Request $request, Facility $facility)
    {
        $input = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'country_subdivision_id' => ['required', 'integer'],
        ]);

        $user = Auth::user();

        $name = data_get($input, 'name');
        $countrySubdivisionId = data_get($input, 'country_subdivision_id');

        if ($facility->tenant_id != $user->tenant_id) {
            throw ValidationException::withMessages([
                'cannot_update' => ['Cannot update this facility'],
            ]);
        }

        $facility->update([
            'name' => $name,
            'country_subdivision_id' => $countrySubdivisionId,
        ]);

        return FacilityResource::make($facility);
    }

    public function destroy(Facility $facility)
    {
        $user = Auth::user();

        if ($facility->tenant_id != $user->tenant_id) {
            throw ValidationException::withMessages([
                'cannot_delete' => ['Cannot delete this facility'],
            ]);
        }

        $facility->delete();
    }

    public function destroyBulk(Request $request)
    {
        $input = $request->validate([
            'ids' => ['required', 'array'],
        ]);

        $ids = data_get($input, 'ids');
        $user = Auth::user();

        $resources = CalendarResource::query()
            ->whereIn('facility_id', $ids)
            ->where('tenant_id', $user->tenant_id)
            ->get();

        if ($resources->count() > 0) {
            throw ValidationException::withMessages([
                'cannot_delete' => ['Cannot delete this facility'],
            ]);
        }

        Facility::query()
            ->whereIn('id', $ids)
            ->where('tenant_id', $user->tenant_id)
            ->delete();

        return response()->noContent();
    }
}
