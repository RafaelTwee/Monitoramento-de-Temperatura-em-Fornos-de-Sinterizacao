<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Google\Client;
use Google\Service\Sheets;

class SheetsController extends Controller
{
    public function index()
    {
        $client = new Client();
        $client->setAuthConfig(base_path('google-credentials.json'));
        $client->addScope(Sheets::SPREADSHEETS_READONLY);

        $service = new Sheets($client);

        $spreadsheetId = '15N7ceBWKeWTykIkRHa2vnxB7DJViqtA_s5-ydLPfmxs'; // Substitua pelo ID da sua planilha
        $range = 'Página1!A2:D'; // Começa na linha 2 e pega todas as colunas até Z

        $response = $service->spreadsheets_values->get($spreadsheetId, $range);
        $values = $response->getValues();

        return view('planilha', ['rows' => $values]);
    }
}