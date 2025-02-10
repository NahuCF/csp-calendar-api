<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CalendarEventResource;
use App\Http\Resources\EventNoteResource;
use App\Models\CalendarEvent;
use App\Models\CalendarResource;
use App\Models\Client;
use App\Models\EventNote;
use App\Models\EventRequest;
use App\Models\User;
use App\Notifications\CancellationSMS;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class CalendarEventController extends Controller
{
    public function index(Request $request)
    {
        $input = $request->validate([
            'calendar_resource_type_id' => ['sometimes', 'integer'],
            'facility_ids' => ['sometimes', 'array'],
            'is_paid' => ['sometimes'],
            'country_subdivision_id' => ['sometimes', 'integer'],
        ]);

        $user = Auth::user();

        $calendarResourceTypeId = data_get($input, 'calendar_resource_type_id');
        $facilityIds = data_get($input, 'facility_ids', []);
        $isPaid = data_get($input, 'is_paid');
        $isPaidBoolean = $isPaid == 'true' ? true : false;
        $countrySubdivisionId = data_get($input, 'country_subdivision_id');

        $calendarEvents = CalendarEvent::query()
            ->with(['resource.facility', 'user'])
            ->when($isPaid, fn ($q) => $q->where('is_paid', $isPaidBoolean))
            ->when(! empty($facilityIds), fn ($q) => $q->whereHas('resource.facility', fn ($query) => $query->whereIn('id', $facilityIds)))
            ->when($countrySubdivisionId, fn ($q) => $q->whereHas('resource.facility', fn ($query) => $query->where('country_subdivision_id', $countrySubdivisionId)))
            ->when($calendarResourceTypeId, fn ($q) => $q->whereHas('resource', fn ($query) => $query->where('calendar_resource_type_id', $calendarResourceTypeId)))
            ->where('rejected', false)
            ->where(fn ($q) => $q->where('will_assist', true)->orWhere('will_assist', null))
            ->where('tenant_id', $user->tenant_id)
            ->get();

        return CalendarEventResource::collection($calendarEvents);
    }

    public function historyClient(Request $request, Client $client)
    {
        $user = Auth::user();

        $history =
            CalendarEvent::query()
                ->with('user')
                ->where('client_id', $client->id)
                ->where('tenant_id', $user->tenant_id)
                ->orderBy('id', 'desc')
                ->get();

        return CalendarEventResource::collection($history);
    }

    public function updateAssistance(Request $request, CalendarEvent $calendarEvent)
    {
        $input = $request->validate([
            'will_assist' => ['required'],
            'cancelation_reason' => ['sometimes'],
        ]);

        $user = Auth::user();

        $client = Client::find($calendarEvent->client_id);

        try {
            User::find($user->id)
                ->notify(new CancellationSMS(
                    number: $client->prefix.$client->cellphone,
                    resource: $calendarEvent->resource->name,
                    facility: $calendarEvent->resource->facility->name,
                    start_date: Carbon::make($calendarEvent->start_at)->format('m/d/Y'),
                    end_date: Carbon::make($calendarEvent->end_at)->format('m/d/Y'),
                    reason: data_get($input, 'cancelation_reason')
                ));
        } catch (\Exception $e) {
        }

        $calendarEvent->cancellation_reason = data_get($input, 'cancelation_reason');
        $calendarEvent->will_assist = (bool) data_get($input, 'will_assist');
        $calendarEvent->save();

        return CalendarEventResource::make($calendarEvent);
    }

    public function store(Request $request)
    {
        $input = $request->validate([
            'name' => ['required', 'string'],
            'calendar_resource_id' => ['required', 'integer'],
            'start_date' => ['required', 'string'],
            'end_date' => ['required', 'string'],
            'start_time' => ['required', 'string'],
            'end_time' => ['required', 'string'],
            'category_id' => ['required'],
            'price' => ['sometimes', 'numeric'],
            'discount_type' => ['sometimes', 'in:percentage,fixed'],
            'discount' => ['sometimes', 'numeric'],
            'discount_percentage' => ['sometimes', 'numeric', 'max:100'],
            'note' => ['sometimes'],
            'client_id' => ['required', 'integer'],
            'is_paid' => ['sometimes'],
            'sport_id' => ['sometimes'],
        ]);

        $name = data_get($input, 'name');
        $calendarResourceId = data_get($input, 'calendar_resource_id');
        $startDate = data_get($input, 'start_date');
        $endDate = data_get($input, 'end_date');
        $startTime = data_get($input, 'start_time');
        $endTime = data_get($input, 'end_time');
        $categoryId = data_get($input, 'category_id');
        $price = data_get($input, 'price');
        $discountType = data_get($input, 'discount_type');
        $discount = data_get($input, 'discount');
        $discountPercentage = data_get($input, 'discount_percentage');
        $note = data_get($input, 'note');
        $clientId = data_get($input, 'client_id');
        $isPaid = data_get($input, 'is_paid') == 'true' ? true : false;
        $sportId = data_get($input, 'sport_id');

        $user = Auth::user();

        if (($discountType == 'percentage' && ! $discountPercentage) || ($discountType == 'fixed' && ! $discount)) {
            throw ValidationException::withMessages([
                'discount' => 'Discount field is missing.',
            ]);
        }

        $client = client::query()
            ->where('id', $clientId)
            ->where('tenant_id', $user->tenant_id)
            ->exists();

        if (! $client) {
            throw validationexception::withmessages([
                'client_id' => 'client not found.',
            ]);
        }

        $startAt = Carbon::make($startDate)->setTimeFromTimeString($startTime);
        $endAt = Carbon::make($endDate)->setTimeFromTimeString($endTime);

        $currencyCode = CalendarResource::find($calendarResourceId)->facility->currency_code;

        $calendarEvent = CalendarEvent::query()
            ->create([
                'name' => $name,
                'tenant_id' => $user->tenant_id,
                'user_id' => $user->id,
                'calendar_resource_id' => $calendarResourceId,
                'category_id' => $categoryId,
                'start_at' => $startAt,
                'price' => $price,
                'discount' => $discountType == 'fixed' ? $discount : null,
                'discount_percentage' => $discountType == 'percentage' ? $discountPercentage : null,
                'type' => 'one-off',
                'end_at' => $endAt,
                'client_id' => $clientId,
                'is_paid' => $isPaid,
                'paid_currency_code' => $isPaid ? $currencyCode : null,
                'sport_id' => $sportId,
            ]);

        if ($note) {
            EventNote::query()
                ->create([
                    'calendar_event_id' => $calendarEvent->id,
                    'user_id' => $user->id,
                    'note' => $note,
                ]);
        }

        $calendarEvent->load('user', 'notes', 'sport');

        return CalendarEventResource::make($calendarEvent);
    }

    public function eventNotes(CalendarEvent $calendarEvent)
    {
        return EventNoteResource::collection($calendarEvent->notes);
    }

    public function storeNote(Request $request, CalendarEvent $calendarEvent)
    {
        $input = $request->validate([
            'note' => ['required', 'string'],
        ]);

        $note = data_get($input, 'note');

        $note = EventNote::query()
            ->create([
                'calendar_event_id' => $calendarEvent->id,
                'user_id' => Auth::user()->id,
                'note' => $note,
            ]);

        $note->load('user');

        return EventNoteResource::make($note);
    }

    public function updateEventRequest(Request $request, CalendarEvent $calendarEvent)
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

        $calendarEvent->update([
            'calendar_resource_id' => $calendarResourceId,
            'start_at' => Carbon::parse($startAtDate.' '.$startTime)->format('Y-m-d H:i:s'),
            'end_at' => Carbon::parse($startAtDate.' '.$endTime)->format('Y-m-d H:i:s'),
            'price' => $price,
        ]);

        $details = CalendarEvent::query()
            ->where('event_request_id', $calendarEvent->event_request_id)
            ->get();

        EventRequest::query()
            ->where('id', $calendarEvent->event_request_id)
            ->update([
                'price' => $details->sum('price'),
            ]);

        CalendarEvent::query()
            ->where('id', $calendarEvent->event_request_id)
            ->update([
                'price' => $details->sum('price'),
            ]);

        return CalendarEventResource::make($calendarEvent);
    }

    public function update(Request $request, CalendarEvent $calendarEvent)
    {
        $input = $request->validate([
            'name' => ['required', 'string'],
            'calendar_resource_id' => ['required', 'integer'],
            'start_date' => ['required', 'string'],
            'end_date' => ['required', 'string'],
            'start_time' => ['required', 'string'],
            'end_time' => ['required', 'string'],
            'category_id' => ['required'],
            'price' => ['sometimes', 'numeric'],
            'discount_type' => ['sometimes', 'in:percentage,fixed'],
            'discount' => ['sometimes', 'numeric'],
            'discount_percentage' => ['sometimes', 'numeric', 'max:100'],
            'will_assist' => ['sometimes'],
            'client_id' => ['required', 'integer'],
            'is_paid' => ['sometimes'],
            'sport_id' => ['sometimes'],
        ]);

        $name = data_get($input, 'name');
        $calendarResourceId = data_get($input, 'calendar_resource_id');
        $startDate = data_get($input, 'start_date');
        $endDate = data_get($input, 'end_date');
        $startTime = data_get($input, 'start_time');
        $endTime = data_get($input, 'end_time');
        $categoryId = data_get($input, 'category_id');
        $price = data_get($input, 'price');
        $discountType = data_get($input, 'discount_type');
        $discount = data_get($input, 'discount');
        $discountPercentage = data_get($input, 'discount_percentage');
        $willAssit = data_get($input, 'will_assist', null);
        $clientId = data_get($input, 'client_id');
        $isPaid = data_get($input, 'is_paid') ? true : false;
        $sportId = data_get($input, 'sport_id');

        $user = Auth::user();

        $client = client::query()
            ->where('id', $clientId)
            ->where('tenant_id', $user->tenant_id)
            ->exists();

        if (! $client) {
            throw validationexception::withmessages([
                'client_id' => 'client not found.',
            ]);
        }

        if (($discountType == 'percentage' && ! $discountPercentage) || ($discountType == 'fixed' && ! $discount)) {
            throw ValidationException::withMessages([
                'discount' => 'Discount field is missing.',
            ]);
        }

        $startAt = Carbon::make($startDate)->setTimeFromTimeString($startTime);
        $endAt = Carbon::make($endDate)->setTimeFromTimeString($endTime);

        $currencyCode = CalendarResource::find($calendarResourceId)->facility->currency_code;

        $calendarEvent
            ->update([
                'name' => $name,
                'tenant_id' => $user->tenant_id,
                'user_id' => $user->id,
                'calendar_resource_id' => $calendarResourceId,
                'category_id' => $categoryId,
                'start_at' => $startAt,
                'end_at' => $endAt,
                'price' => $price,
                'discount' => $discountType == 'fixed' ? $discount : null,
                'discount_percentage' => $discountType == 'percentage' ? $discountPercentage : null,
                'will_assist' => $willAssit,
                'client_id' => $clientId,
                'is_paid' => $isPaid,
                'paid_currency_code' => $isPaid ? $currencyCode : null,
                'sport_id' => $sportId,
            ]);

        if ($calendarEvent->event_request_id) {
            $details = CalendarEvent::query()
                ->where('event_request_id', $calendarEvent->event_request_id)
                ->get();

            EventRequest::query()
                ->where('id', $calendarEvent->event_request_id)
                ->update([
                    'price' => $details->sum('price'),
                ]);
        }

        $updatedEvent = CalendarEvent::query()
            ->find($calendarEvent->id)
            ->load('user', 'sport', 'notes');

        return CalendarEventResource::make($updatedEvent);
    }

    public function storeBulk(Request $request)
    {
        $input = $request->validate([
            'reservations' => ['required', 'array'],
            'reservations.*.start_at' => ['required', 'string'],
            'reservations.*.end_at' => ['required', 'string'],
            'reservations.*.calendar_resource_id' => ['required', 'string'],
            'name' => ['required', 'string'],
            'category_id' => ['required'],
            'client_id' => ['required'],
            'price' => ['sometimes', 'numeric'],
            'is_paid' => ['sometimes'],
            'discount_type' => ['sometimes', 'in:percentage,fixed'],
            'discount' => ['sometimes', 'numeric'],
            'discount_percentage' => ['sometimes', 'numeric', 'max:100'],
        ]);

        $reservations = data_get($input, 'reservations');
        $name = data_get($input, 'name');
        $categoryId = data_get($input, 'category_id');
        $price = data_get($input, 'price');
        $discountType = data_get($input, 'discount_type');
        $discount = data_get($input, 'discount');
        $discountPercentage = data_get($input, 'discount_percentage');
        $clientId = data_get($input, 'client_id');
        $isPaid = data_get($input, 'is_paid') ? true : false;

        if (($discountType == 'percentage' && ! $discountPercentage) || ($discountType == 'fixed' && ! $discount)) {
            throw ValidationException::withMessages([
                'discount' => 'Discount field is missing.',
            ]);
        }

        $user = Auth::user();

        $dataToInsert = collect($reservations)->map(function ($reservation) use ($user, $name, $clientId, $categoryId, $price, $discount, $discountType, $discountPercentage, $isPaid) {
            $startAt = Carbon::make($reservation['start_at']);
            $endAt = Carbon::make($reservation['end_at']);

            return [
                'name' => $name,
                'tenant_id' => $user->tenant_id,
                'user_id' => $user->id,
                'calendar_resource_id' => $reservation['calendar_resource_id'],
                'start_at' => $startAt,
                'end_at' => $endAt,
                'category_id' => $categoryId,
                'client_id' => $clientId,
                'price' => $price,
                'discount' => $discountType == 'fixed' ? $discount : null,
                'discount_percentage' => $discountType == 'percentage' ? $discountPercentage : null,
                'is_paid' => $isPaid,
                'type' => 'recurrent',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        });

        CalendarEvent::query()->insert($dataToInsert->toArray());

        return response()->json([], 201);
    }

    public function validateIntervals(Request $request)
    {
        $input = $request->validate([
            'calendar_resource_id' => ['required', 'integer'],
            'start_date' => ['required', 'string'],
            'end_date' => ['required', 'string'],
            'start_time' => ['required', 'string'],
            'end_time' => ['required', 'string'],
            'interval' => ['sometimes', 'integer'],
            'pattern' => ['sometimes', 'string'],
            'is_recurrent' => ['sometimes'],
            'days_selected' => ['sometimes', 'array'],
            'repeat_until' => ['sometimes', 'string'],
            'repeat_until_date' => ['sometimes', 'string'],
            'repeat_until_numeric' => ['sometimes', 'integer'],
        ]);

        $calendarResourceId = data_get($input, 'calendar_resource_id');
        $startDate = data_get($input, 'start_date');
        $endDate = data_get($input, 'end_date');
        $startTime = data_get($input, 'start_time');
        $endTime = data_get($input, 'end_time', null);
        $interval = (int) data_get($input, 'interval');
        $pattern = data_get($input, 'pattern');
        $daysSelected = data_get($input, 'days_selected');
        $repeatUntilDate = data_get($input, 'repeat_until_date');
        $repeatUntilDate = $repeatUntilDate ? Carbon::parse($repeatUntilDate)->endOfDay() : null;
        $repeatUntil = data_get($input, 'repeat_until');
        $repeatUntilNumeric = data_get($input, 'repeat_until_numeric');
        $isRecurrent = data_get($input, 'is_recurrent') == 'true' ? true : false;

        $startAt = Carbon::make($startDate)->setTimeFromTimeString($startTime);
        $endAt = Carbon::make($endDate)->setTimeFromTimeString($endTime);

        $events = CalendarEvent::query()
            //->where('start_at', '>=', $startDateStartOfDay)
            //->where('end_at', '<=', $endDateEndOfDay)
            ->where('calendar_resource_id', $calendarResourceId)
            ->get();

        $resource = CalendarResource::find($calendarResourceId);

        $intervals = $isRecurrent
            ? $this->generateRecurrentIntervals($startAt, $endAt, $interval, $pattern, $daysSelected, $repeatUntil == 'numeric' ? $repeatUntilNumeric : $repeatUntilDate)
            : $this->generateNonRecurrentIntervals($startAt, $endAt, $interval);

        $intervalsWithData = $intervals->map(function ($interval) use ($events, $resource) {
            $startAt = Carbon::make($interval['start_at']);
            $endAt = Carbon::make($interval['end_at']);

            $conflicts = $events->filter(function ($event) use ($startAt, $endAt) {
                $eventStart = Carbon::parse($event->start_at);
                $eventEnd = Carbon::parse($event->end_at);

                return
                    ($startAt->greaterThanOrEqualTo($eventStart) && $endAt->lessThanOrEqualTo($eventEnd)) ||
                    ($startAt->lessThanOrEqualTo($eventStart) && $endAt->greaterThanOrEqualTo($eventEnd)) ||
                    ($startAt->lessThanOrEqualTo($eventStart) && $endAt->lessThanOrEqualTo($eventEnd) && $endAt->greaterThan($eventStart)) ||
                    ($startAt->greaterThanOrEqualTo($eventStart) && $startAt->lessThan($eventEnd) && $endAt->greaterThanOrEqualTo($eventEnd));
            });

            return [
                'start_at' => $startAt,
                'end_at' => $endAt,
                'has_conflict' => $conflicts->count() > 0,
                'conflicts' => $conflicts,
                'resource_id' => $resource->id,
                'resource_name' => $resource->name,
                'start_time' => $startAt->format('H:i'),
                'end_time' => $endAt->format('H:i'),
            ];
        });

        return response()->json([
            'data' => $intervalsWithData,
        ]);
    }

    private function generateNonRecurrentIntervals($startAt, $endAt, $interval = 0)
    {
        $dates = [];

        $startCopy = $startAt->copy();
        if ($interval !== 0) {
            while ($startCopy->lt($endAt)) {
                $dates[] = [
                    'start_at' => $startCopy->format('Y-m-d H:i:s'),
                    'end_at' => $startCopy->clone()->addMinutes($interval)->format('Y-m-d H:i:s'),
                ];
                $startCopy->addMinutes($interval);
            }
        } else {

            $dates[] = [
                'start_at' => $startAt->format('Y-m-d H:i:s'),
                'end_at' => $endAt->format('Y-m-d H:i:s'),
            ];
        }

        return collect($dates);
    }

    private function generateRecurrentIntervals($startAt, $endAt, $interval, $pattern, $days, $until, $offset = 1)
    {
        $dates = [];
        $startAt = Carbon::parse($startAt);
        $endAt = Carbon::parse($endAt);

        if (is_numeric($until)) {
            $maxReservations = (int) $until;
        } else {
            $until = Carbon::parse($until);
            $maxReservations = null;
        }

        if ($pattern === 'by_weekly') {
            $offset = 14;
        } elseif ($pattern === 'monthly') {
            $offset = 30;
        }

        if (in_array($pattern, ['by_weekly', 'daily'])) {
            $days = [0, 1, 2, 3, 4, 5, 6];
        }

        if ($interval !== 0) {
            $startInterval = $startAt->copy();
            while ($startInterval->lt($endAt)) {
                $dates[] = [
                    'start_at' => $startInterval->format('Y-m-d H:i:s'),
                    'end_at' => $startInterval->copy()->addMinutes($interval)->format('Y-m-d H:i:s'),
                ];
                $startInterval->addMinutes($interval);
            }
        } else {
            $dates[] = [
                'start_at' => $startAt->format('Y-m-d H:i:s'),
                'end_at' => $endAt->format('Y-m-d H:i:s'),
            ];
        }

        $currentDate = $startAt->copy()->addDay();
        $finishDate = $endAt->copy()->addDay();

        while ($maxReservations === null ? $currentDate->lte($until) : count($dates) < $maxReservations) {
            if (in_array($currentDate->dayOfWeek, $days)) {
                $startDate = $currentDate->copy()->setTime($startAt->hour, $startAt->minute, 0);
                $endDate = $finishDate->copy()->setTime($endAt->hour, $endAt->minute, 0);

                if ($interval !== 0) {
                    $startInterval = $startDate->copy();
                    while ($startInterval->lt($endDate)) {
                        $dates[] = [
                            'start_at' => $startInterval->format('Y-m-d H:i:s'),
                            'end_at' => $startInterval->copy()->addMinutes($interval)->format('Y-m-d H:i:s'),
                        ];
                        $startInterval->addMinutes($interval);
                    }
                } else {
                    $dates[] = [
                        'start_at' => $startDate->format('Y-m-d H:i:s'),
                        'end_at' => $endDate->format('Y-m-d H:i:s'),
                    ];
                }
            }

            $currentDate->addDays($offset);
            $finishDate->addDays($offset);
        }

        return collect($dates);
    }
}
