<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\SheetsController;
use App\Http\Controllers\GraficoController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| Rotas de Autenticação
|--------------------------------------------------------------------------
*/
Route::get('login',  [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login']);
Route::post('logout',[LoginController::class, 'logout'])->name('logout');


/*
|--------------------------------------------------------------------------
| Página Inicial
|--------------------------------------------------------------------------
*/
// Se não estiver autenticado, redireciona para a tela de login
Route::get('/', fn() => redirect()->route('home'))->middleware('guest');

// Se estiver autenticado, mostra a view 'welcome' com os experimentos
Route::get('/home', function() {
    $experimentos = app(SheetsController::class)->getExperimentos();
    return view('welcome', ['experimentos' => $experimentos]);
})->middleware('auth')->name('home');

/*
|--------------------------------------------------------------------------
| Rotas Protegidas (Auth)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    // Atualizar apenas o nome
    Route::patch('/experimentos/{id}/nome', [SheetsController::class, 'updateNome'])
         ->name('experimentos.updateNome');

    // Excluir experimento
    Route::delete('/experimentos/{id}', [SheetsController::class, 'destroy'])
         ->name('experimentos.destroy');

    // Excluir por intervalo
    Route::post('/experimentos/destroy-range', [SheetsController::class, 'destroyRange'])
         ->name('experimentos.destroyRange');

    // Baixar experimento em Excel
    Route::get('experimentos/{id}/download-excel', [GraficoController::class, 'downloadExcel'])
         ->name('experimentos.downloadExcel');

    // Rotas da planilha
    Route::controller(SheetsController::class)->group(function () {
        Route::get('/planilha', 'index')->name('planilha.index');
    });

    // Rotas de gráficos
    Route::controller(GraficoController::class)->group(function () {
        Route::get('/experimentos', 'index')->name('experimentos.index');
        Route::get('/experimentos/{id}', 'show')->name('experimentos.grafico');
    });


    // Rota de usuários
    Route::get('/users', [UserController::class, 'index'])
     ->name('users.index');

     // Formulário de edição
    Route::get('/users/{name}/edit', [UserController::class, 'edit'])
        ->name('users.edit');

    // Atualização propriamente dita
    Route::put('/users/{name}', [UserController::class, 'update'])
        ->name('users.update');

    Route::delete('/users/{name}', [UserController::class, 'destroy'])
     ->name('users.destroy');

    // Exibir formulário de criação
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/users',       [UserController::class, 'store'])->name('users.store');


});
