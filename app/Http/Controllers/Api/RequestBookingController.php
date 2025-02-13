<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventRequestResource;
use App\Models\CalendarEvent;
use App\Models\Client;
use App\Models\EventRequest;
use App\Models\EventRequestDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RequestBookingController extends Controller
{
    public function index(Request $request)
    {
        $input = $request->validate([
            'client_id' => ['sometimes'],
        ]);

        $clientId = data_get($input, 'client_id');

        $user = Auth::user();

        if ($clientId) {
            $client = Client::find($clientId);
        }

        $eventRequest = EventRequest::query()
            ->with('details.resource.facility', 'sport', 'facility')
            ->where('tenant_id', $user->tenant_id)
            ->when($clientId, fn ($q) => $q->where('user_id', $client->user_id))
            ->orderBy('id', 'desc')
            ->paginate(15);

        return EventRequestResource::collection($eventRequest);

    }

    public function updateDetail(Request $request, EventRequestDetail $detail)
    {
        $input = $request->validate([
            'calendar_resource_id' => ['required'],
            'start_time' => ['required'],
            'end_time' => ['required'],
            'price' => ['required'],
            'start_at_date' => ['required'],
        ]);

        $calendarResourceId = data_get($input, 'calendar_resource_id');
        $startTime = data_get($input, 'start_time');
        $endTime = data_get($input, 'end_time');
        $price = data_get($input, 'price');
        $startAtDate = data_get($input, 'start_at_date');

        $detail->update([
            'calendar_resource_id' => $calendarResourceId,
            'start_at' => Carbon::parse($startAtDate.' '.$startTime)->format('Y-m-d H:i:s'),
            'end_at' => Carbon::parse($startAtDate.' '.$endTime)->format('Y-m-d H:i:s'),
            'price' => $price,
        ]);

        $details = EventRequestDetail::query()
            ->where('event_request_id', $detail->event_request_id)
            ->get();

        EventRequest::query()
            ->where('id', $detail->event_request_id)
            ->update([
                'price' => $details->sum('price'),
            ]);

        return response()->json($detail);
    }

    public function rejectRequest(Request $request, EventRequest $eventRequest)
    {
        if ($eventRequest->rejected === true) {
            return response()->json('', 200);
        }

        $calendarEvents = CalendarEvent::query()
            ->where('event_request_id', $eventRequest->id)
            ->get();

        CalendarEvent::query()
            ->whereIn('id', $calendarEvents->pluck('id'))
            ->update([
                'rejected' => true,
            ]);

        $eventRequest->update([
            'rejected' => true,
        ]);

        return response()->json('', 200);
    }

    public function confirmRequest(Request $request, EventRequest $eventRequest)
    {
        if ($eventRequest->confirmed === true) {
            return response()->json('', 200);
        }

        $eventRequest->update([
            'confirmed' => true,
        ]);

        $details = EventRequestDetail::query()
            ->where('event_request_id', $eventRequest->id)
            ->get();

        $dataToInsert = [];

        $user = Auth::user();

        $client = Client::query()
            ->where('user_id', $eventRequest->user_id)
            ->first();

        foreach ($details as $detail) {
            $dataToInsert[] = [
                'name' => 'Client Reservation',
                'client_id' => $client->id,
                'calendar_resource_id' => $detail->calendar_resource_id,
                'user_id' => $user->id,
                'tenant_id' => $eventRequest->tenant_id,
                'price' => $detail->price,
                'category_id' => 1,
                'start_at' => $detail->start_at,
                'end_at' => $detail->end_at,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        CalendarEvent::query()
            ->insert($dataToInsert);

        return response()->json($details);
    }
}
