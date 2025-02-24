<?php

namespace App\Services;

use App\Models\CalendarEvent;
use App\Models\EventRequest;

class OrderService
{
    public function updateOrderBalances($orderId)
    {
        $order = EventRequest::query()
            ->where('id', $orderId)
            ->first();

        $events = CalendarEvent::query()
            ->where('event_request_id', $orderId)
            ->get();

        $price = $events->sum('price');
        $taxesTotalAmount = $events->sum('taxes_amount');
        $discountTotalAmount = $events->sum('discount_amount');

        $order->update([
            'price' => $price,
            'discount_amount' => $discountTotalAmount,
            'tax_amount' => $taxesTotalAmount,
            'total_to_pay' => $price + $taxesTotalAmount - $discountTotalAmount,
        ]);
    }
}
