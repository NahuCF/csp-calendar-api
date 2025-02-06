<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function reservations(Request $request)
    {
        $input = $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date'],
        ]);

        $startDate = Carbon::make(data_get($input, 'start_date'));
        $endDate = Carbon::make(data_get($input, 'end_date'));

        $user = Auth::user();

        $data = (new ReportService)->generateReservationReport($startDate, $endDate, $user->tenant_id);

        return response()->json($data);
    }
}
