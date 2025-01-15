<?php

use App\Http\Controllers\Api\CalendarResourceTypeController;
use App\Http\Controllers\Recrivals\AuthController;
use App\Http\Controllers\Recrivals\CalendarEventController;
use App\Http\Controllers\Recrivals\CalendarResourceController;
use App\Http\Controllers\Recrivals\FacilityController;
use App\Http\Controllers\Recrivals\RequestBookingController;
use Illuminate\Support\Facades\Route;

Route::post('auth/register', [AuthController::class, 'register']);
Route::get('calendar-resources', [CalendarResourceController::class, 'index'])->name('calendar-resource.index');
Route::get('calendar-resource-types', [CalendarResourceTypeController::class, 'index'])->name('calendar-resource-types.index');

Route::get('facilities/with-events', [FacilityController::class, 'facilitiesWithEvents']);
Route::resource('facilities', FacilityController::class);

Route::get('calendar-events', [CalendarEventController::class, 'index'])->name('calendar-events.index');

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('request-bookings', [RequestBookingController::class, 'index'])->name('request-booking.index');
    Route::post('request-bookings', [RequestBookingController::class, 'requestBooking'])->name('request-booking');
});
