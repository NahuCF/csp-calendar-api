<?php

namespace App\Exports;

use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class CancellationExport implements FromView
{
    public function __construct(public $data) {}

    public function view(): View
    {
        return view('dowloads.cancellation', [
            'cancellationsByFacility' => collect($this->data)->groupBy('facility_name'),
        ]);
    }
}
