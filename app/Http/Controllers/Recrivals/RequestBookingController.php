<?php

namespace App\Http\Controllers\Recrivals;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventRequestDetailResource;
use App\Http\Resources\EventRequestResource;
use App\Models\CalendarResource;
use App\Models\EventRequest;
use App\Models\EventRequestDetail;
use App\Models\Tenant;
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
            ->with('details.resource')
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
            'identifier' => ['required'],
        ]);

        $events = data_get($input, 'events');
        $identifier = data_get($input, 'identifier');

        $tenant = Tenant::query()
            ->where('identifier', $identifier)
            ->first();

        if (! $tenant) {
            throw ValidationException::withMessages([
                'tenant' => ['Tenant not found'],
            ]);
        }

        $user = Auth::user();

        $resourcesById = CalendarResource::query()
            ->where('tenant_id', $tenant->id)
            ->whereIn('id', collect($events)->pluck('resource_id'))
            ->get()
            ->keyBy('id');

        $eventRequest = EventRequest::query()
            ->create([
                'tenant_id' => $tenant->id,
                'user_id' => $user->id,
                'request_id' => EventRequest::query()
                    ->where('tenant_id', $tenant->id)
                    ->select('request_id')
                    ->max('request_id') + 1,
                'price' => collect($events)->map(function ($event) use ($resourcesById) {
                    return $this->calculateRequestEventPrice(
                        $event['start'],
                        $event['end'],
                        $resourcesById->get($event['resource_id'])->price
                    );
                })->sum(),
            ]);

        $details = [];

        foreach ($events as $event) {
            $detail = EventRequestDetail::query()
                ->create([
                    'event_request_id' => $eventRequest->id,
                    'tenant_id' => $tenant->id,
                    'user_id' => $user->id,
                    'calendar_resource_id' => $event['resource_id'],
                    'price' => $this->calculateRequestEventPrice(
                        $event['start'],
                        $event['end'],
                        $resourcesById->get($event['resource_id'])->price),
                    'start_at' => $event['start'],
                    'end_at' => $event['end'],
                ]);
            $details[] = $detail;
        }

        return EventRequestResource::make($eventRequest)->additional([
            'meta' => [
                'detail' => EventRequestDetailResource::collection($details),
            ],
        ]);
    }

    private function calculateRequestEventPrice($start, $end, $price)
    {
        $start = Carbon::parse($start);
        $end = Carbon::parse($end);

        $halfHours = abs($end->diffInMinutes($start) / 30);

        return $halfHours * $price;
    }
}
