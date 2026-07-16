<?php

use App\Http\Controllers\HolidayController;
use App\Http\Controllers\ReservationController;
use Illuminate\Support\Facades\Route;

Route::get('/slots', [ReservationController::class, 'slots'])->name('slots.index');
Route::post('/reservations', [ReservationController::class, 'store'])->name('slots.store');
Route::delete('/reservations/{reservation}', [ReservationController::class, 'destroy'])->name('slots.destroy');

Route::post('/holiday', [HolidayController::class, 'store'])->name('holiday.store');
