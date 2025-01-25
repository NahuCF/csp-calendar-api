<?php

namespace App\Http\Controllers\Api;

use Stripe\Stripe;
use App\Models\Client;
use App\Models\EventRequest;
use Illuminate\Http\Request;
use App\Models\CalendarEvent;
use App\Models\CalendarResource;
use App\Models\EventRequestDetail;
use App\Models\StripeIntentRequest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class StripeController extends Controller
{
    public function createIntent(Request $request)
    {
        $input = $request->validate([
            'request_id' => ['required'],
        ]);

        Stripe::setApiKey(env('STRIPE_SECRET'));

        $requestId = data_get($input, 'request_id');

        $user = Auth::user();

        $eventRequest = EventRequest::query()
            ->where('request_id', $requestId)
            ->where('user_id', $user->id)
            ->first();

        $resource = CalendarResource::find($eventRequest->calendar_resource_id);

        $paymentIntent = \Stripe\PaymentIntent::create([
            'amount' => $eventRequest->price * 100,  // In cents
            'currency' => strtolower($resource->facility->currency_code),
            'automatic_payment_methods' => ['enabled' => true],
        ]);

        StripeIntentRequest::query()
            ->create([
                'event_request_id' => $eventRequest->id,
                'intent_id' => $paymentIntent->id,
                'tenant_id' => $user->tenant_id,
                'data' => json_encode($paymentIntent->toArray()),
                'status' => $paymentIntent->status,
            ]);

        return response()->json([
            'intent' => $paymentIntent,
            'clientSecret' => $paymentIntent->client_secret,
            'request' => $eventRequest,
            'currency_code' => $resource->facility->currency_code
        ]);
    }

    public function confirmIntent(Request $request)
    {
        $input = $request->validate([
            'intent_id' => ['required'],
            'event_request_id' => ['required'],
        ]);

        $user = Auth::user();

        $intentId = data_get($input, 'intent_id');
        $eventRequestId = data_get($input, 'event_request_id');

        StripeIntentRequest::query()
            ->where('intent_id', $intentId)
            ->where('tenant_id', $user->tenant_id)
            ->where('event_request_id', $eventRequestId)
            ->update([
                'status' => 'succeeded',
            ]);

        $details = EventRequestDetail::query()
            ->where('event_request_id', $eventRequestId)
            ->get();

        $eventRequest = EventRequest::find($eventRequestId);
        $eventRequest->update([
            'confirmed' => true,
            'is_paid' => true,
        ]);

        $dataToInsert = [];

        $client = Client::query()
            ->where('user_id', $user->id)
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
                'is_paid' => true,
                'end_at' => $detail->end_at,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        CalendarEvent::query()
            ->insert($dataToInsert);

        return response()->json([], 200);
    }
}
