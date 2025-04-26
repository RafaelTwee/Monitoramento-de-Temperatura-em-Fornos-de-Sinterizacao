<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SheetsController extends Controller
{
    public function index()
    {
        return view('planilha'); // Vai tentar abrir uma view chamada "planilha.blade.php"
    }
}
