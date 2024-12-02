<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CalendarResourceResource;
use App\Models\CalendarEvent;
use App\Models\CalendarResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class CalendarResourceController extends Controller
{
    public function index(Request $request)
    {
        $input = $request->validate([
            'search' => ['sometimes'],
        ]);

        $user = Auth::user();

        $search = data_get($input, 'search');

        $resources = CalendarResource::query()
            ->withCount('events')
            ->with('user', 'facility', 'calendarResourceType')
            ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->where('tenant_id', $user->tenant_id)
            ->orderBy('id', 'asc')
            ->paginate(15);

        return CalendarResourceResource::collection($resources);
    }

    public function update(Request $request, CalendarResource $calendarResource)
    {
        $input = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'calendar_resource_type_id' => ['required', 'integer'],
            'facility_id' => ['required', 'integer'],
        ]);

        $name = data_get($input, 'name');
        $calendarResourceTypeId = data_get($input, 'calendar_resource_type_id');
        $facilityId = data_get($input, 'facility_id');

        $calendarResource->update([
            'name' => $name,
            'calendar_resource_type_id' => $calendarResourceTypeId,
            'facility_id' => $facilityId,
        ]);

        return CalendarResourceResource::make($calendarResource);
    }

    public function store(Request $request)
    {
        $input = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'calendar_resource_type_id' => ['required', 'integer'],
            'facility_id' => ['required', 'integer'],
        ]);

        $user = Auth::user();

        $name = data_get($input, 'name');
        $calendarResourceTypeId = data_get($input, 'calendar_resource_type_id');
        $facilityId = data_get($input, 'facility_id');

        $resource = CalendarResource::query()
            ->create([
                'name' => $name,
                'tenant_id' => $user->tenant_id,
                'user_id' => $user->id,
                'calendar_resource_type_id' => $calendarResourceTypeId,
                'facility_id' => $facilityId,
            ]);

        return calendarresourceresource::make($resource);
    }

    public function destroy(CalendarResource $calendarResource)
    {
        $user = Auth::user();

        if ($calendarResource->tenant_id !== $user->tenant_id) {
            throw ValidationException::withMessages([
                'resource' => ['You can not delete this resource'],
            ]);
        }

        CalendarEvent::query()
            ->where('calendar_resource_id', $calendarResource->id)
            ->where('tenant_id', $user->tenant_id)
            ->delete();

        $calendarResource->delete();

        return response()->noContent();
    }

    public function destroyBulk(Request $request)
    {
        $input = $request->validate([
            'ids' => ['required', 'array'],
        ]);

        $ids = data_get($input, 'ids');
        $user = Auth::user();

        CalendarEvent::query()
            ->whereIn('calendar_resource_id', $ids)
            ->where('tenant_id', $user->tenant_id)
            ->delete();

        CalendarResource::query()
            ->whereIn('id', $ids)
            ->where('tenant_id', $user->tenant_id)
            ->delete();

        return response()->noContent();
    }
}
