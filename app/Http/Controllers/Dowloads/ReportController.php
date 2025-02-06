<?php

namespace App\Http\Controllers\Dowloads;

use App\Exports\ReservationExport;
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

        return Excel::download(new ReservationExport($data), 'Reservation Report.xlsx');
    }
}
