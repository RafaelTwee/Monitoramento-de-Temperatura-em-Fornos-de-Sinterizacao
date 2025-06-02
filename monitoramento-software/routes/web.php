<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SheetsController;
use App\Http\Controllers\GraficoController;

// Página principal
Route::get('/', function () {
    $experimentos = app(SheetsController::class)->getExperimentos();
    return view('welcome', ['experimentos' => $experimentos]);
})->name('welcome');

// Rota para atualizar apenas o nome
Route::patch('/experimentos/{id}/nome', [SheetsController::class, 'updateNome'])->name('experimentos.updateNome');

// Rota para excluir experimento
Route::delete('/experimentos/{id}', [SheetsController::class, 'destroy'])->name('experimentos.destroy');

Route::post('/experimentos/destroy-range',[SheetsController::class, 'destroyRange'])->name('experimentos.destroyRange');

// Rota para baixar experimento
Route::get('experimentos/{id}/download-excel',[GraficoController::class, 'downloadExcel'])->name('experimentos.downloadExcel');

// Rotas da planilha
Route::controller(SheetsController::class)->group(function () {
    Route::get('/planilha', 'index');
});

// Rotas de gráficos
Route::controller(GraficoController::class)->group(function () {
    Route::get('/experimentos', 'index')->name('experimentos.index');
    Route::get('/experimentos/{id}', 'show')->name('experimentos.grafico');
});