<?php

namespace App\Http\Controllers\Dowloads;

use App\Http\Controllers\Controller;
use App\Models\CalendarEvent;
use App\Models\CalendarResource;
use App\Models\Client;
use App\Models\EventRequest;
use App\Models\Sport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class InvoiceController extends Controller
{
    public function invoicePreview(Request $request)
    {
        $input = $request->validate([
            'date' => ['required', 'date'],
            'resource_id' => ['required', 'integer'],
            'start_time' => ['required', 'string'],
            'end_time' => ['required', 'string'],
            'discount' => ['required', 'numeric'],
            'sport_id' => ['required', 'integer'],
            'client_id' => ['required', 'integer'],
        ]);

        $date = Carbon::make(data_get($input, 'date'))->format('l, F d Y');
        $resourceId = data_get($input, 'resource_id');
        $discount = (float) data_get($input, 'discount');
        $sportId = data_get($input, 'sport_id');
        $startTime = Carbon::createFromFormat('H:i', data_get($input, 'start_time'))->format('h:i A');
        $endTime = Carbon::createFromFormat('H:i', data_get($input, 'end_time'))->format('h:i A');
        $todayDate = Carbon::now()->format('l, F d Y');
        $clientId = data_get($input, 'client_id');

        $resource = CalendarResource::find($resourceId);
        $clientName = Client::find($clientId)->name;

        $currency = $resource->facility->currency_code;
        $facilityName = $resource->facility->name;
        $resourceName = $resource->name;

        $pdf = Pdf::loadView('pdf.invoice', ['data' => 'Your data here']);

        $dateFile = Carbon::now()->format('d-m-Y');
        $nameFile = 'Invoice-'.$clientName.'-'.$dateFile.'.pdf';

        return $pdf->download($nameFile);
    }

    public function orderInvoice(Request $request)
    {
        $input = $request->validate([
            'tenant_id' => ['required', 'string'],
            'order_id' => ['required', 'integer'],
        ]);

        $tenantId = data_get($input, 'tenant_id');
        $orderId = data_get($input, 'order_id');

        $calendarEvents = CalendarEvent::query()
            ->where('tenant_id', $tenantId)
            ->where('event_request_id', $orderId)
            ->orderBy('start_at', 'asc')
            ->get();

        $calendarResources = CalendarResource::query()
            ->where('tenant_id', $tenantId)
            ->whereIn('id', $calendarEvents->pluck('calendar_resource_id'))
            ->get()
            ->keyBy('id');

        $order = EventRequest::query()
            ->find($orderId);

        $sport = Sport::find($order->sport_id);
        $clientName = Client::find($order->client_id)->name;

        $currencyName = $calendarResources->get($calendarEvents->first()->calendar_resource_id)->facility->currency_code;
        $facilityName = $calendarResources->get($calendarEvents->first()->calendar_resource_id)->facility->name;

        $position = 1;
        $data = $calendarEvents->map(function ($calendarEvent) use ($position, $calendarResources, $sport, $currencyName, $facilityName) {
            $start = Carbon::parse($calendarEvent->start_at);
            $end = Carbon::parse($calendarEvent->end_at);

            return [
                'position' => $position++,
                'date' => $start->format('l, F j Y'),
                'resource_name' => $calendarResources->get($calendarEvent->calendar_resource_id)->name,
                'total_to_pay' => $calendarEvent->total_to_pay,
                'discount_amount' => $calendarEvent->discount_amount,
                'sport_name' => $sport->name,
                'times' => $start->format('g:i A').'-'.$end->format('g:i A'),
                'currency_name' => $currencyName,
                'facility_name' => $facilityName,
            ];
        });

        $dataTotals = [
            'total_price' => $calendarEvents->sum('price'),
            'total_discount' => $calendarEvents->sum('discount_amount'),
            'total_tax' => $calendarEvents->sum('taxes_amount'),
            'total_price_after_taxes' => $calendarEvents->sum('total_to_pay'),
            'amount_paid' => 0, // TODO CHANGE
            'amount_due' => $calendarEvents->sum('total_to_pay'), // TODO CHANGE
        ];

        $pdf = Pdf::loadView('pdf.invoice', [
            'data' => $data,
            'dataTotals' => $dataTotals,
            'invoiceNumber' => $order->request_id,
            'logo' => base64_encode(file_get_contents(public_path('storage/csp-logo.png'))),
            'clientName' => $clientName,
            'currencyName' => $currencyName,
            'date' => Carbon::now()->format('F d, Y'),
        ]);

        $fileName = "Invoice $order->request_id for $clientName.pdf";

        return $pdf->stream($fileName);
    }
}
