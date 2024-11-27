<?php

use App\Models\User;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\CountryController;
use App\Http\Controllers\Api\FacilityController;
use App\Http\Controllers\Api\CalendarEventController;
use App\Http\Controllers\Api\PasswordResetController;
use App\Http\Controllers\Api\CalendarResourceController;
use App\Http\Controllers\Api\CalendarResourceTypeController;

Route::get('/', function () {
    return response()->json(User::all());
});

// Unprotected auth
Route::post('auth/login', [AuthController::class, 'login']);
Route::post('auth/register', [AuthController::class, 'register']);

// Password reset
Route::post('password/code', [PasswordResetController::class, 'sendResetCode']);
Route::post('password/verify', [PasswordResetController::class, 'verifyCode']);
Route::post('password/reset', [PasswordResetController::class, 'reset']);

Route::apiResource('users', UserController::class)
    ->except(['store']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('roles', [RoleController::class, 'index'])->name('roles.index');
    Route::post('calendar-resources/destroy-bulk', [CalendarResourceController::class, 'destroyBulk'])->name('calendar-resources.destroy-bulk');
    Route::resource('calendar-resources', CalendarResourceController::class);
    Route::get('calendar-resource-types', [CalendarResourceTypeController::class, 'index'])->name('calendar-resource-types.index');
    Route::get('facilities/with-events', [FacilityController::class, 'facilitiesWithEvents']);
    Route::resource('facilities', FacilityController::class);
    Route::post('calendar-events/store-bulk', [CalendarEventController::class, 'storeBulk'])->name('calendar-events.store-bulk');
    Route::post('calendar-events/validate-intervals', [CalendarEventController::class, 'validateIntervals'])->name('calendar-events.validate-intervals');
    Route::resource('calendar-events', CalendarEventController::class);
    Route::get('countries/with-events', [CountryController::class, 'countriesWithEvents']);
});

Route::resource('countries', CountryController::class);
