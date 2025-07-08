<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\SheetsController;
use App\Http\Controllers\GraficoController;

// Login / Logout / Register
Route::get ('/login',  [AuthController::class,'showLoginForm'])->name('login.form');
Route::post('/login',  [AuthController::class,'login'])->name('login');
Route::post('/logout', [AuthController::class,'logout'])->name('logout');

Route::get ('/register', [AuthController::class,'showRegisterForm'])->name('register.form');
Route::post('/register', [AuthController::class,'register'])->name('register');

// Rotas protegidas (usuário logado)
Route::middleware('auth.user')->group(function() {
  // página inicial
  Route::get('/', function(){
    $exp = app(SheetsController::class)->getExperimentos();
    return view('welcome',['experimentos'=>$exp]);
  })->name('welcome');

  // gráficos, downloads etc.
  Route::get('experimentos/{id}', [GraficoController::class,'show'])->name('experimentos.grafico');
  Route::get('experimentos/{id}/download-excel',[GraficoController::class,'downloadExcel'])
      ->name('experimentos.downloadExcel');

  // CRUD de usuários — só admins
  Route::middleware('auth.admin')->prefix('users')->group(function(){
    Route::get   ('/',       [UsersController::class,'index'])->name('users.index');
    Route::get   ('create',  [UsersController::class,'create'])->name('users.create');
    Route::post  ('store',   [UsersController::class,'store'])->name('users.store');
    Route::get   ('{row}/edit',[UsersController::class,'edit'])->name('users.edit');
    Route::patch ('{row}',   [UsersController::class,'update'])->name('users.update');
    Route::delete('{row}',   [UsersController::class,'destroy'])->name('users.destroy');
  });
});
