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

    public function generateCancellationReport($startDate, $endDate, $tenantId)
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
            ->where('will_assist', false)
            ->get()
            ->sortBy('resource.facility.name');

        return collect($events)->map(function ($event) use ($resourcesById) {
            $resource = $resourcesById->get($event->calendar_resource_id);

            return [
                'id' => $event->id,
                'name' => $event->name ?: 'Client created',
                'facility_name' => $resource->facility->name,
                'resource_name' => $resource->name,
                'reason' => $event->cancellation_reason,
                'date' => Carbon::make($event->created_at)->format('m-d-Y'),
            ];
        })->toArray();
    }

    public function generateSalesReport($startDate, $endDate, $tenantId)
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
            ->where('is_paid', true)
            ->get()
            ->sortBy('resource.facility.name');

        $mostFrequentResources = $events->groupBy('calendar_resource_id')
            ->map(function ($events) {
                return $events->count();
            })
            ->sort()
            ->reverse()
            ->take(3)
            ->keys();

        return [
            'transactions' => $events->count(),
            'avg_transaction_price_usd' => round($events->where('paid_currency_code', 'USD')->avg('price'), 2),
            'avg_transaction_price_cad' => round($events->where('paid_currency_code', 'CAD')->avg('price'), 2),
            'total_revenue_cad' => round($events->where('paid_currency_code', 'CAD')->sum('price'), 2),
            'total_revenue_usd' => round($events->where('paid_currency_code', 'USD')->sum('price'), 2),
            'top_resources' => $mostFrequentResources->map(function ($resourceId) use ($resourcesById, $events) {
                $resource = $resourcesById->get($resourceId);

                return [
                    'id' => $resourceId,
                    'name' => $resource->name,
                    'facility_name' => $resource->facility->name,
                    'events' => $events->where('calendar_resource_id', $resourceId)->count(),
                ];
            }),

        ];
    }
}
