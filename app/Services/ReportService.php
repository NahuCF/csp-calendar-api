<?php

namespace App\Services;

use App\Models\CalendarEvent;
use App\Models\CalendarResource;
use Carbon\Carbon;

class ReportService
{
    public function generateReservationReport($startDate, $endDate, $tenantId)
    {
        $resourcesById = CalendarResource::query()
            ->with('facility')
            ->where('tenant_id', $tenantId)
            ->get()
            ->keyBy('id');

        $events = CalendarEvent::query()
            ->with('resource.facility')
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->where('tenant_id', $tenantId)
            ->where('rejected', false)
            ->get()
            ->sortBy('resource.facility.name');

        return collect($events)->map(function ($event) use ($resourcesById) {
            $resource = $resourcesById->get($event->calendar_resource_id);

            return [
                'id' => $event->id,
                'name' => $event->name ?: 'Client created',
                'facility_name' => $resource->facility->name,
                'resource_name' => $resource->name,
                'currency' => $resource->facility->currency_code,
                'price' => $event->price,
                'is_paid' => $event->is_paid,
                'date' => Carbon::make($event->created_at)->format('m-d-Y'),
            ];
        })->toArray();
    }
}
