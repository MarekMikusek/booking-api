<?php

use App\Http\Controllers\ReservationController;
use Illuminate\Support\Facades\Route;

Route::get('/slots', [ReservationController::class, 'slots'])->name('slots.index');
Route::post('/reservations', [ReservationController::class, 'store'])->name('slots.store');
Route::delete('/reservations/{reservation}', [ReservationController::class, 'destroy'])->name('slots.destroy');
