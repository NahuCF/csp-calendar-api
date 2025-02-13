<?php

namespace App\Http\Controllers\Dowloads;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\CalendarResource;
use App\Http\Controllers\Controller;

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
        $discount = (float)data_get($input, 'discount');   
        $sportId = data_get($input, 'sport_id');
        $startTime = Carbon::createFromFormat('H:i', data_get($input, 'start_time'))->format('h:i A');
        $endTime = Carbon::createFromFormat('H:i', data_get($input, 'end_time'))->format('h:i A');
        $todayDate = Carbon::now()->format('l, F d Y');
        $clientId = data_get($input, 'client_id');

        $resource = CalendarResource::find($resourceId);
        $clientName = Client::find($clientId)->name;

        $currency =  $resource->facility->currency_code;
        $facilityName = $resource->facility->name;
        $resourceName = $resource->name;
        
        $pdf = Pdf::loadView('pdf.invoice', ['data' => 'Your data here']);


        $dateFile= Carbon::now()->format('d-m-Y');
        $nameFile = 'Invoice-' . $clientName . '-' . $dateFile . '.pdf';

        return $pdf->download($nameFile);
    }
}
