<?php

namespace App\Http\Controllers\Api;

use App\Models\EventRequest;
use Illuminate\Http\Request;
use App\Models\CalendarEvent;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\OrderResource;
use App\Http\Resources\CalendarEventResource;

class OrderController extends Controller
{
    public function show(Request $request, $orderId)
    {
        $auth = Auth::user();

        $order = EventRequest::query()
            ->where('request_id', $orderId)
            ->where('tenant_id', $auth->tenant_id)
            ->first();

        $order->load(['details' => function ($query) {
            $query->orderBy('start_at');
        }, 'details.resource', 'sport']);

        // Assign sequential numbers to the sorted details
        $order->details->each(function ($detail, $index) {
            $detail->number = $index + 1;
        });

        return OrderResource::make($order)->additional([
            'meta' => [
                'total' => $order->price,
                'discount_amount' => $order->discount_amount,
                'tax_amount' => $order->tax_amount,
                'total_to_pay' => $order->total_to_pay,
                'amount_paid' => 0,
                'amount_due' => $order->total_to_pay,
            ],
        ]);
    }

    public function updateEvent(Request $request, EventRequest $order, CalendarEvent $event)
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

        $event->update([
            'calendar_resource_id' => $calendarResourceId,
            'start_at' => Carbon::parse($startAtDate.' '.$startTime)->format('Y-m-d H:i:s'),
            'end_at' => Carbon::parse($startAtDate.' '.$endTime)->format('Y-m-d H:i:s'),
            'price' => $price,
            'total_to_pay' => $price + $event->taxes_amount - $event->discount_amount,
        ]);

        $orderDetails = CalendarEvent::query()
            ->where('event_request_id', $event->event_request_id)
            ->get();

        $priceOrder = $orderDetails->sum('price');
        $priceTotalOrder = $orderDetails->sum('total_to_pay');

        EventRequest::query()
            ->where('id', $event->event_request_id)
            ->update([
                'price' => $priceOrder,
                'total_to_pay' => $priceTotalOrder,
            ]);

        return CalendarEventResource::make($event)->additional([
            'total' => number_format($priceOrder, 2),
        ]);
    }
}
