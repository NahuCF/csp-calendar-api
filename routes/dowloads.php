<?php

use App\Http\Controllers\Dowloads\ReportController;
use Illuminate\Support\Facades\Route;

Route::get('reports/reservations', [ReportController::class, 'reservations']);
Route::get('reports/cancellations', [ReportController::class, 'cancellations']);
Route::get('reports/sales', [ReportController::class, 'sales']);
