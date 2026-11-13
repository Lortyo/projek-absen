<?php

use App\Http\Controllers\GajiController;
use App\Http\Controllers\TukangController;
use App\Http\Controllers\PeriodeController;
use Illuminate\Support\Facades\Route;

Route::get('/',[GajiController::class, 'index'])->name('home');
Route::post('/tukang/store', [TukangController::class, 'store'])->name('tukang.store');
Route::post('/periode/store', [PeriodeController::class, 'store'])->name('periode.store');
Route::post('/tahun/store', [PeriodeController::class, 'storeTahun'])->name('tahun.store');
Route::get('/filter', [GajiController::class, 'filter'])->name('gaji.filter');