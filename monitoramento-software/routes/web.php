<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SheetsController;

Route::get('/planilha', [SheetsController::class, 'index']);


Route::get('/', function () {
    return view('welcome');
});


