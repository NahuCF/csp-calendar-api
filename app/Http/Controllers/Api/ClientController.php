<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClientResource;
use App\Models\CalendarEvent;
use App\Models\Client;
use App\Models\EventNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ClientController extends Controller
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

        $clients = Client::query()
            ->withCount('events')
            ->where('tenant_id', $user->tenant_id)
            ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('id', 'asc');

        $clients = $paginated ? $clients->paginate(15) : $clients->get();

        return ClientResource::collection($clients);
    }

    public function store(Request $request)
    {
        $input = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'cellphone' => ['required', 'string', 'max:255'],
            'prefix' => ['required', 'string', 'max:255'],
        ]);

        $user = Auth::user();

        $name = data_get($input, 'name');
        $cellphone = data_get($input, 'cellphone');
        $prefix = data_get($input, 'prefix');

        $client = Client::query()
            ->create([
                'name' => $name,
                'cellphone' => $cellphone,
                'user_id' => $user->id,
                'tenant_id' => $user->tenant_id,
                'prefix' => $prefix,
            ]);

        $client->load('user');

        return ClientResource::make($client);
    }

    public function update(Request $request, Client $client)
    {
        $input = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'cellphone' => ['required', 'string', 'max:255'],
            'prefix' => ['required', 'string', 'max:255'],
        ]);

        $user = Auth::user();

        $name = data_get($input, 'name');
        $cellphone = data_get($input, 'cellphone');
        $prefix = data_get($input, 'prefix');

        if ($user->tenant_id !== $client->tenant_id) {
            throw ValidationException::withMessages([
                'client' => 'Client not found',
            ]);
        }

        $client->update([
            'name' => $name,
            'cellphone' => $cellphone,
            'prefix' => $prefix,
        ]);

        $client->load('user');

        return ClientResource::make($client);
    }

    public function destroy(Client $client)
    {
        $user = Auth::user();

        if ($client->tenant_id != $user->tenant_id) {
            throw ValidationException::withMessages([
                'cannot_delete' => ['Cannot delete this client'],
            ]);
        }

        $eventIds = CalendarEvent::query()
            ->select('id')
            ->where('client_id', $client->id)
            ->where('tenant_id', $user->tenant_id)
            ->get()
            ->pluck('id');

        EventNote::query()
            ->whereIn('calendar_event_id', $eventIds)
            ->delete();

        CalendarEvent::query()
            ->whereIn('id', $eventIds)
            ->delete();

        $client->delete();
    }

    public function destroyBulk(Request $request)
    {
        $input = $request->validate([
            'ids' => ['required', 'array'],
        ]);

        $ids = data_get($input, 'ids');
        $user = Auth::user();

        $eventIds = CalendarEvent::query()
            ->select('id')
            ->whereIn('client_id', $ids)
            ->where('tenant_id', $user->tenant_id)
            ->get()
            ->pluck('id');

        EventNote::query()
            ->whereIn('calendar_event_id', $eventIds)
            ->delete();

        CalendarEvent::query()
            ->whereIn('id', $eventIds)
            ->delete();

        Client::query()
            ->whereIn('id', $ids)
            ->where('tenant_id', $user->tenant_id)
            ->delete();

        return response()->noContent();
    }
}
