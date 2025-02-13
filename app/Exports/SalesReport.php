<?php

namespace App\Exports;

use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class SalesReport implements FromView
{
    public function __construct(public $data) {}

    public function view(): View
    {
        return view('dowloads.sales', [
            'salesReport' => $this->data,
        ]);
    }
}
