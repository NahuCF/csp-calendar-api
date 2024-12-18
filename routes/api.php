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
use App\Http\Controllers\Api\TimezoneController;
use App\Http\Controllers\Api\UserController;
use App\Http\Middleware\CheckPermission;
use Illuminate\Support\Facades\Route;

// Unprotected auth
Route::post('auth/login', [AuthController::class, 'login']);
Route::post('auth/register', [AuthController::class, 'register']);
Route::post('auth/logout', [AuthController::class, 'logout']);

// Password reset
Route::post('password/code', [PasswordResetController::class, 'sendResetCode']);
Route::post('password/verify', [PasswordResetController::class, 'verifyCode']);
Route::post('password/reset', [PasswordResetController::class, 'reset']);

Route::get('/timezones', [TimezoneController::class, 'index']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('users/me', [UserController::class, 'me'])->name('users.me');
    Route::post('users', [UserController::class, 'store'])->name('users.store')->middleware(CheckPermission::class.':Create Users');
    Route::post('users/destroy-bulk', [UserController::class, 'destroyBulk'])->name('users.destroy-bulk');
    Route::get('users', [UserController::class, 'index'])->name('users.index');
    Route::get('users/emails', [UserController::class, 'emails'])->name('users.emails');
    Route::get('users/{user}', [UserController::class, 'show'])->name('users.show');
    Route::match(['post', 'put'], 'users/{user}', [UserController::class, 'update'])->name('users.update');

    Route::get('permissions', [PermissionController::class, 'index'])->name('permissions.index');
    Route::post('roles/destroy-bulk', [RoleController::class, 'destroyBulk'])->name('roles.destroy-bulk');
    Route::resource('roles', RoleController::class);

    Route::post('calendar-resources/destroy-bulk', [CalendarResourceController::class, 'destroyBulk'])->name('calendar-resources.destroy-bulk');
    Route::get('calendar-resources', [CalendarResourceController::class, 'index'])->name('calendar-resource.index')->middleware(CheckPermission::class.':View Calendar');
    Route::put('calendar-resources/{calendar_resource}', [CalendarResourceController::class, 'update'])->name('calendar-resource.update');
    Route::post('calendar-resources', [CalendarResourceController::class, 'store'])->name('calendar-resource.store')->middleware(CheckPermission::class.':Create Resources');
    Route::delete('calendar-resource/{calendar_resource}', [CalendarResourceController::class, 'destroy'])->name('calendar-resource.delete');
    Route::get('calendar-resource-types', [CalendarResourceTypeController::class, 'index'])->name('calendar-resource-types.index');

    Route::get('facilities/with-events', [FacilityController::class, 'facilitiesWithEvents']);
    Route::post('facilities/destroy-bulk', [FacilityController::class, 'destroyBulk'])->name('facilities.destroy-bulk');
    Route::resource('facilities', FacilityController::class);

    Route::post('calendar-events/store-bulk', [CalendarEventController::class, 'storeBulk'])->name('calendar-events.store-bulk');
    Route::post('calendar-events/validate-intervals', [CalendarEventController::class, 'validateIntervals'])->name('calendar-events.validate-intervals');
    Route::get('calendar-events', [CalendarEventController::class, 'index'])->name('calendar-events.index')->middleware(CheckPermission::class.':View Calendar');
    Route::post('calendar-events', [CalendarEventController::class, 'store'])->name('calendar-events.store')->middleware(CheckPermission::class.':Create Reservation');
    Route::get('calendar-events/{calendar_event}/edit', [CalendarEventController::class, 'edit'])->name('calendar-events.edit')->middleware(CheckPermission::class.':Edit Calendar');
    Route::put('calendar-events/{calendar_event}', [CalendarEventController::class, 'update'])->name('calendar-events.update')->middleware(CheckPermission::class.':Edit Calendar');
    Route::delete('calendar-events/{calendar_event}', [CalendarEventController::class, 'destroy'])->name('calendar-events.destroy')->middleware(CheckPermission::class.':Delete Reservation');

    Route::get('countries/with-events', [CountryController::class, 'countriesWithEvents']);
});

Route::resource('countries', CountryController::class);
