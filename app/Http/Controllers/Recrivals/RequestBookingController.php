<?php

namespace App\Http\Controllers\Recrivals;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventRequestResource;
use App\Models\CalendarEvent;
use App\Models\CalendarResource;
use App\Models\Category;
use App\Models\Client;
use App\Models\EventRequest;
use App\Models\Facility;
use App\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class RequestBookingController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $eventRequest = EventRequest::query()
            ->with('details.resource.facility', 'sport', 'facility')
            ->where('user_id', $user->id)
            ->orderBy('id', 'desc')
            ->paginate(15);

        return EventRequestResource::collection($eventRequest);

    }

    public function requestBooking(Request $request)
    {
        $input = $request->validate([
            'events' => ['required'],
            'events.*.start' => ['required'],
            'events.*.end' => ['required'],
            'events.*.resource_id' => ['required'],
            'events.*.facility_id' => ['required'],
            'identifier' => ['required'],
            'notes' => ['sometimes'],
            'resource_id' => ['required'],
            'facility_id' => ['required'],
            'sport_id' => ['required'],
        ]);

        $events = data_get($input, 'events');
        $identifier = data_get($input, 'identifier');
        $notes = data_get($input, 'notes', '');
        $sportId = data_get($input, 'sport_id');
        $resourceId = data_get($input, 'resource_id');
        $facilityId = data_get($input, 'facility_id');

        $tenant = Tenant::query()
            ->where('identifier', $identifier)
            ->first();

        if (! $tenant) {
            throw ValidationException::withMessages([
                'tenant' => ['Tenant not found'],
            ]);
        }

        $user = Auth::user();

        $client = Client::query()
            ->where('user_id', $user->id)
            ->first();

        if (! User::find($user->id)->hasRole('Guest') || ! $client) {
            throw ValidationException::withMessages([
                'credentials' => ['Client not found'],
            ]);
        }

        $resource = CalendarResource::query()
            ->where('tenant_id', $tenant->id)
            ->where('id', $resourceId)
            ->first();

        $clientCategory = Category::query()
            ->whereNull('tenant_id')
            ->whereNull('user_id')
            ->where('name', 'Client')
            ->first();

        $facilitiesById = Facility::query()
            ->whereIn('id', collect($events)->pluck('facility_id'))
            ->get()
            ->keyBy('id');

        $eventRequest = EventRequest::query()
            ->create([
                'tenant_id' => $tenant->id,
                'user_id' => $user->id,
                'notes' => $notes,
                'sport_id' => $sportId,
                'calendar_resource_id' => $resourceId,
                'facility_id' => $facilityId,
                'request_id' => EventRequest::query()
                    ->where('tenant_id', $tenant->id)
                    ->select('request_id')
                    ->max('request_id') + 1,
                'price' => collect($events)->map(function ($event) use ($resource, $facilitiesById) {
                    return $this->calculateRequestEventPrice(
                        $event['start'],
                        $event['end'],
                        $resource->price,
                        $facilitiesById->get($event['facility_id'])->tax_percentage
                    );
                })->sum(),
            ]);

        $dataToInsert = [];

        foreach ($events as $event) {
            $dataToInsert[] = [
                'name' => '',
                'client_id' => $client->id,
                'category_id' => $clientCategory->id,
                'calendar_resource_id' => $event['resource_id'],
                'user_id' => $user->id,
                'sport_id' => $sportId,
                'tenant_id' => $eventRequest->tenant_id,
                'price' => $this->calculateRequestEventPrice(
                    $event['start'],
                    $event['end'],
                    $resource->price,
                    $facilitiesById->get($event['facility_id'])->tax_percentage
                ),
                'start_at' => $event['start'],
                'end_at' => $event['end'],
                'event_request_id' => $eventRequest->id,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        CalendarEvent::query()
            ->insert($dataToInsert);

        return response()->json([], 200);
    }

    private function calculateRequestEventPrice($start, $end, $price, $taxPercentage)
    {
        $start = Carbon::parse($start);
        $end = Carbon::parse($end);

        $halfHours = abs($end->diffInMinutes($start) / 30);

        if ($taxPercentage) {
            $price = $price + ($price * $taxPercentage / 100);
        }

        return $halfHours * $price;
    }
}
