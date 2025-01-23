<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FacilityResource;
use App\Models\CalendarEvent;
use App\Models\CalendarResource;
use App\Models\CalendarResourceType;
use App\Models\CountrySubdivision;
use App\Models\Facility;
use GuzzleHttp\Client;
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
            'only_with_resources' => ['sometimes'],
        ]);

        $paginated = data_get($input, 'paginated', false);
        $search = data_get($input, 'search');
        $onlyWithResources = data_get($input, 'only_with_resources', false);

        $user = Auth::user();

        $facilities = Facility::query()
            ->with('user', 'country')
            ->withCount('resources')
            ->where('tenant_id', $user->tenant_id)
            ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->when($onlyWithResources, fn ($q) => $q->has('resources'))
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
            'lat' => ['required', 'numeric'],
            'lng' => ['required', 'numeric'],
            'currency_code' => ['required', 'string', 'max:255'],
        ]);

        $user = Auth::user();

        $name = data_get($input, 'name');
        $lat = data_get($input, 'lat');
        $lng = data_get($input, 'lng');
        $currencyCode = data_get($input, 'currency_code');

        $territory = $this->territoryData($lng, $lat);
        $subTerritory = $territory ? $territory['subterritory'] : null;

        $countrySubdivision = null;
        if ($subTerritory) {
            $countrySubdivision = CountrySubdivision::query()
                ->where('name', 'like', "%{$subTerritory}%")
                ->first();
        }

        $facility = Facility::query()
            ->create([
                'name' => $name,
                'user_id' => $user->id,
                'tenant_id' => $user->tenant_id,
                'country_id' => $countrySubdivision ? $countrySubdivision->country_id : null,
                'country_subdivision_id' => $countrySubdivision ? $countrySubdivision->id : null,
                'fallback_subterritory_name' => $countrySubdivision ? null : $subTerritory,
                'fallback_territory_name' => $countrySubdivision ? null : $territory['territory'],
                'currency_code' => $currencyCode,
                'lat' => $lat,
                'lng' => $lng,
            ]);

        return FacilityResource::make($facility);
    }

    public function update(Request $request, Facility $facility)
    {
        $input = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'lat' => ['required', 'numeric'],
            'lng' => ['required', 'numeric'],
            'currency_code' => ['required', 'string', 'max:255'],
        ]);

        $user = Auth::user();

        $name = data_get($input, 'name');
        $lat = data_get($input, 'lat');
        $lng = data_get($input, 'lng');
        $currencyCode = data_get($input, 'currency_code');

        $user = Auth::user();

        if ($facility->tenant_id != $user->tenant_id) {
            throw ValidationException::withMessages([
                'cannot_update' => ['Cannot update this facility'],
            ]);
        }

        $territory = $this->territoryData($lng, $lat);
        $subTerritory = $territory ? $territory['subterritory'] : null;

        $countrySubdivision = null;
        if ($subTerritory) {
            $countrySubdivision = CountrySubdivision::query()
                ->where('name', 'like', "%{$subTerritory}%")
                ->first();
        }

        $facility->update([
            'name' => $name,
            'country_id' => $countrySubdivision ? $countrySubdivision->country_id : null,
            'country_subdivision_id' => $countrySubdivision ? $countrySubdivision->id : null,
            'fallback_subterritory_name' => $countrySubdivision ? null : $subTerritory,
            'fallback_territory_name' => $countrySubdivision ? null : $territory['territory'],
            'currency_code' => $currencyCode,
            'lat' => $lat,
            'lng' => $lng,
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

    private function territoryData($lng, $lat)
    {
        $client = new Client;
        $response = $client->get('https://nominatim.openstreetmap.org/reverse', [
            'query' => [
                'lat' => $lat,
                'lon' => $lng,
                'format' => 'json',
                'addressdetails' => 1,
            ],
            'headers' => [
                'User-Agent' => 'MyApp/1.0 (nahuefer173@gamil.com)',
            ],
        ]);

        $data = json_decode($response->getBody(), true);

        $state = isset($data['address']['state']) ? $data['address']['state'] : null;
        $country = isset($data['address']['country']) ? $data['address']['country'] : null;

        if ($state && $country) {
            return [
                'subterritory' => $state,
                'territory' => $country,
            ];
        } else {
            return false;
        }
    }
}
