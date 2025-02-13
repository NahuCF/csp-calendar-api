<?php

namespace App\Http\Controllers\Dowloads;

use App\Exports\CancellationExport;
use App\Exports\ReservationExport;
use App\Exports\SalesReport;
use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function reservations(Request $request)
    {
        $input = $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date'],
            'tenant_id' => ['required'],
        ]);

        $startDate = Carbon::make(data_get($input, 'start_date'));
        $endDate = Carbon::make(data_get($input, 'end_date'));
        $tenantId = data_get($input, 'tenant_id');

        $data = (new ReportService)->generateReservationReport($startDate, $endDate, $tenantId);

        $nameFile = 'Reservation Report '.$startDate->format('m-d-Y').' to '.$endDate->format('m-d-Y').'.xlsx';

        return Excel::download(new ReservationExport($data), $nameFile);
    }

    public function cancellations(Request $request)
    {
        $input = $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date'],
            'tenant_id' => ['required'],
        ]);

        $startDate = Carbon::make(data_get($input, 'start_date'));
        $endDate = Carbon::make(data_get($input, 'end_date'));
        $tenantId = data_get($input, 'tenant_id');

        $data = (new ReportService)->generateCancellationReport($startDate, $endDate, $tenantId);

        $nameFile = 'Cancellation Report '.$startDate->format('m-d-Y').' to '.$endDate->format('m-d-Y').'.xlsx';

        return Excel::download(new CancellationExport($data), $nameFile);
    }

    public function sales(Request $request)
    {
        $input = $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date'],
            'tenant_id' => ['required'],
        ]);

        $startDate = Carbon::make(data_get($input, 'start_date'));
        $endDate = Carbon::make(data_get($input, 'end_date'));
        $tenantId = data_get($input, 'tenant_id');

        $data = (new ReportService)->generateSalesReport($startDate, $endDate, $tenantId);

        $nameFile = 'Sales Report '.$startDate->format('m-d-Y').' to '.$endDate->format('m-d-Y').'.xlsx';

        return Excel::download(new SalesReport($data), $nameFile);
    }
}
