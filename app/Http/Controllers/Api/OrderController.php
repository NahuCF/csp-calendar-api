<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CalendarEventResource;
use App\Http\Resources\OrderResource;
use App\Models\CalendarEvent;
use App\Models\EventRequest;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

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
        }, 'details.resource', 'sport', 'client']);

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
                'amount_paid' => number_format(0, 2),
                'amount_due' => $order->total_to_pay,
            ],
        ]);
    }

    public function updateNote(Request $request, EventRequest $order)
    {
        $input = $request->validate([
            'note' => ['sometimes'],
        ]);

        $order->update([
            'note' => data_get($input, 'note'),
        ]);

        return OrderResource::make($order);
    }

    public function updateEvent(Request $request, EventRequest $order, CalendarEvent $event)
    {
        $input = $request->validate([
            'calendar_resource_id' => ['required'],
            'start_time' => ['required'],
            'end_time' => ['required'],
            'price' => ['required'],
            'discount_amount' => ['required'],
            'start_at_date' => ['required'],
        ]);

        $calendarResourceId = data_get($input, 'calendar_resource_id');
        $startTime = data_get($input, 'start_time');
        $endTime = data_get($input, 'end_time');
        $price = data_get($input, 'price');
        $startAtDate = data_get($input, 'start_at_date');
        $discountAmount = data_get($input, 'discount_amount');

        $event->update([
            'calendar_resource_id' => $calendarResourceId,
            'start_at' => Carbon::parse($startAtDate.' '.$startTime)->format('Y-m-d H:i:s'),
            'end_at' => Carbon::parse($startAtDate.' '.$endTime)->format('Y-m-d H:i:s'),
            'price' => $price,
            'total_to_pay' => $price + $event->taxes_amount - $event->discount_amount,
            'discount_amount' => $discountAmount,
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

        (new OrderService)->updateOrderBalances($event->event_request_id);

        return CalendarEventResource::make($event)->additional([
            'total' => number_format($priceOrder, 2),
        ]);
    }

    public function confirm(Request $request, EventRequest $order)
    {
        if ($order->confirmed === true) {
            return response()->json('', 200);
        }

        $user = Auth::user();

        $order->update([
            'confirmed' => true,
            'rejected' => false,
        ]);

        CalendarEvent::query()
            ->where('tenant_id', $user->tenant_id)
            ->where('event_request_id', $order->id)
            ->update([
                'confirmed' => true,
                'rejected' => false,
            ]);

        return response()->json('', 200);
    }

    public function cancel(Request $request, EventRequest $order)
    {
        if ($order->rejected === true) {
            return response()->json('', 200);
        }

        $user = Auth::user();

        $order->update([
            'rejected' => true,
            'confirmed' => false,
        ]);

        CalendarEvent::query()
            ->where('tenant_id', $user->tenant_id)
            ->where('event_request_id', $order->id)
            ->update([
                'confirmed' => false,
                'rejected' => true,
            ]);

        return response()->json('', 200);
    }
}
