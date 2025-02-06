<?php

namespace App\Exports;

use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class ReservationExport implements FromView
{
    public function __construct(public $data) {}

    public function view(): View
    {
        return view('dowloads.reservation', [
            'reservationsByFacility' => collect($this->data)->groupBy('facility_name'),
        ]);
    }
}
