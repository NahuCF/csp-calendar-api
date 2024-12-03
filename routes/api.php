<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CalendarEventController;
use App\Http\Controllers\Api\CalendarResourceController;
use App\Http\Controllers\Api\CalendarResourceTypeController;
use App\Http\Controllers\Api\CountryController;
use App\Http\Controllers\Api\FacilityController;
use App\Http\Controllers\Api\PasswordResetController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

// Unprotected auth
Route::post('auth/login', [AuthController::class, 'login']);
Route::post('auth/register', [AuthController::class, 'register']);

// Password reset
Route::post('password/code', [PasswordResetController::class, 'sendResetCode']);
Route::post('password/verify', [PasswordResetController::class, 'verifyCode']);
Route::post('password/reset', [PasswordResetController::class, 'reset']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('users/destroy-bulk', [UserController::class, 'destroyBulk'])->name('users.destroy-bulk');
    Route::get('users/emails', [UserController::class, 'emails'])->name('users.emails');
    Route::apiResource('users', UserController::class);
    Route::get('permissions', [PermissionController::class, 'index'])->name('permissions.index');
    Route::post('roles/destroy-bulk', [RoleController::class, 'destroyBulk'])->name('roles.destroy-bulk');
    Route::resource('roles', RoleController::class);
    Route::post('calendar-resources/destroy-bulk', [CalendarResourceController::class, 'destroyBulk'])->name('calendar-resources.destroy-bulk');
    Route::resource('calendar-resources', CalendarResourceController::class);
    Route::get('calendar-resource-types', [CalendarResourceTypeController::class, 'index'])->name('calendar-resource-types.index');
    Route::get('facilities/with-events', [FacilityController::class, 'facilitiesWithEvents']);
    Route::post('facilities/destroy-bulk', [FacilityController::class, 'destroyBulk'])->name('facilities.destroy-bulk');
    Route::resource('facilities', FacilityController::class);
    Route::post('calendar-events/store-bulk', [CalendarEventController::class, 'storeBulk'])->name('calendar-events.store-bulk');
    Route::post('calendar-events/validate-intervals', [CalendarEventController::class, 'validateIntervals'])->name('calendar-events.validate-intervals');
    Route::resource('calendar-events', CalendarEventController::class);
    Route::get('countries/with-events', [CountryController::class, 'countriesWithEvents']);
});

Route::resource('countries', CountryController::class);
