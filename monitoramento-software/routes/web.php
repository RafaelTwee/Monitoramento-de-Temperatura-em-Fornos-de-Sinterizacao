<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SheetsController;
use App\Http\Controllers\GraficoController;

// Página principal
Route::get('/', function () {
    return view('welcome');
});

// Rotas da planilha
Route::controller(SheetsController::class)->group(function () {
    Route::get('/planilha', 'index');
});

// Rotas de gráficos
Route::controller(GraficoController::class)->group(function () {
    Route::get('/experimentos', 'index')->name('experimentos.index');
    Route::get('/experimentos/{id}', 'show')->name('experimentos.show');
});