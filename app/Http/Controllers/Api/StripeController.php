<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CalendarEvent;
use App\Models\CalendarResource;
use App\Models\EventRequest;
use App\Models\StripeIntentRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Stripe\Stripe;

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

        $configId = env('STRIPE_PAYMENT_METHOD_CONFIGURATION');

        $intentPayload = [
            'amount' => $eventRequest->price * 100,  // In cents
            'currency' => strtolower($resource->facility->currency_code),
            'automatic_payment_methods' => ['enabled' => true],
        ];

        if ($configId) {
            $intentPayload['payment_method_configuration'] = $configId;
        }

        return response()->json($configId);

        $paymentIntent = \Stripe\PaymentIntent::create($intentPayload);

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
            'currency_code' => $resource->facility->currency_code,
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

        $eventRequest = EventRequest::find($eventRequestId);
        $eventRequest->update([
            'confirmed' => true,
            'is_paid' => true,
        ]);

        CalendarEvent::query()
            ->where('event_request_id', $eventRequestId)
            ->update([
                'confirmed' => true,
                'is_paid' => true,
            ]);

        return response()->json([], 200);
    }
}
