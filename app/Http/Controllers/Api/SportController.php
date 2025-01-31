<?php

namespace App\Http\Controllers\Api;

use App\Models\Sport;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\SportResource;
use App\Models\CalendarEvent;
use Illuminate\Validation\ValidationException;

class SportController extends Controller
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

        $sports = Sport::query()
            ->with('user')
            ->withCount('events')
            ->where('tenant_id', $user->tenant_id)
            ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('id', 'asc');

        $sports = $paginated ? $sports->paginate(15) : $sports->get();

        return SportResource::collection($sports);
    }

    public function store(Request $request)
    {
        $input = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'icon' => ['required'],
        ]);

        $user = Auth::user();

        $name = data_get($input, 'name');
        $icon = data_get($input, 'icon');

        $sport = Sport::query()
            ->create([
                'name' => $name,
                'user_id' => $user->id,
                'tenant_id' => $user->tenant_id,
                'icon' => $icon,
            ]);

        return SportResource::make($sport);
    }

    public function update(Request $request, Sport $sport)
    {
        $input = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'icon' => ['required'],
        ]);

        $user = Auth::user();

        $name = data_get($input, 'name');
        $icon = data_get($input, 'icon');

        $user = Auth::user();

        if ($sport->tenant_id != $user->tenant_id) {
            throw ValidationException::withMessages([
                'cannot_update' => ['Cannot update this sport'],
            ]);
        }

        $sport->update([
            'name' => $name,
            'icon' => $icon,
        ]);

        return SportResource::make($sport);
    }

    public function destroy(Sport $sport)
    {
        $user = Auth::user();

        if ($sport->tenant_id != $user->tenant_id) {
            throw ValidationException::withMessages([
                'cannot_delete' => ['Cannot delete this sport'],
            ]);
        }

        if ($sport->events->count() > 0) {
            throw ValidationException::withMessages([
                'cannot_delete' => ['Cannot delete sport with asociated reservations.'],
            ]);
        }

        $sport->delete();
    }

    public function destroyBulk(Request $request)
    {
        $input = $request->validate([
            'ids' => ['required', 'array'],
        ]);

        $ids = data_get($input, 'ids');
        $user = Auth::user();

        $calendarEvent = CalendarEvent::query()
            ->whereIn('sport_id', $ids)
            ->where('tenant_id', $user->tenant_id)
            ->get();

        if ($calendarEvent->count() > 0) {
            throw ValidationException::withMessages([
                'cannot_delete' => ['Cannot delete some sport.'],
            ]);
        }

        Sport::query()
            ->whereIn('id', $ids)
            ->where('tenant_id', $user->tenant_id)
            ->delete();

        return response()->noContent();
    }
}
