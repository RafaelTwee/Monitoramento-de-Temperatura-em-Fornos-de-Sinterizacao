<?php

namespace App\Http\Controllers;

use Google\Client;
use Google\Service\Sheets;
use Carbon\Carbon;

class SheetsController extends Controller
{
    public function index()
    {
        $experimentos = $this->getExperimentos();
        return view('planilha', ['experimentos' => $experimentos]);
    }
    
    public function getExperimentos() // Renomeie index() para getExperimentos()
    {
        $client = new Client();
        $client->setAuthConfig(base_path('google-credentials.json'));
        $client->addScope(Sheets::SPREADSHEETS_READONLY);

        $service = new Sheets($client);
        $spreadsheetId = '15N7ceBWKeWTykIkRHa2vnxB7DJViqtA_s5-ydLPfmxs';
        $range = 'Página1!A2:D';

        $response = $service->spreadsheets_values->get($spreadsheetId, $range);
        $values = $response->getValues();

        $experimentos = [];
        $experimentoAtual = [];
        $contador = 1;
        $dataInicio = null;
        $processandoExperimento = false;

        foreach ($values as $row) {
            if (!empty($row[0])) { // Encontrou uma data/hora
                if (!$processandoExperimento) {
                    // Primeira data - início do experimento
                    $dataInicio = $row[0];
                    $processandoExperimento = true;
                    
                    // Adiciona os dados da linha inicial
                    if (isset($row[1])) {
                        $experimentoAtual[] = [
                            'tempo' => $row[1],
                            'temperatura' => $row[2] ?? null
                        ];
                    }
                } else {
                    // Adiciona os dados desta linha ANTES de finalizar o experimento
                    if (isset($row[1])) {
                        $experimentoAtual[] = [
                            'tempo' => $row[1],
                            'temperatura' => $row[2] ?? null
                        ];
                    }
                    
                    // Marca o fim do experimento atual
                    $experimentos[] = $this->criarExperimento($contador, $dataInicio, $row[0], $experimentoAtual);
                    $contador++;
                    
                    // Reseta para aguardar próximo experimento
                    $processandoExperimento = false;
                    $experimentoAtual = [];
                }
            } elseif ($processandoExperimento) {
                // Linha de dados normal (coluna A vazia) durante um experimento
                if (isset($row[1])) {
                    $experimentoAtual[] = [
                        'tempo' => $row[1],
                        'temperatura' => $row[2] ?? null
                    ];
                }
            }
        }

        // Corrigindo o nome do método (de criarExperimento para criarExperimento)
        if ($processandoExperimento && !empty($experimentoAtual)) {
            $experimentos[] = $this->criarExperimento($contador, $dataInicio, null, $experimentoAtual);
        }

        return $experimentos; // Retorna apenas o array de experimentos, sem a view
    }

    private function criarExperimento($numero, $inicio, $fim, $dados)
    {
        // Processa cada medição para garantir formato numérico correto
        $dadosProcessados = [];
        foreach ($dados as $medicao) {
            $dadosProcessados[] = [
                'tempo' => str_replace(',', '.', $medicao['tempo']),
                'temperatura' => str_replace(',', '.', $medicao['temperatura'])
            ];
        }
    
        return [
            'id' => $numero - 1,
            'nome' => "Experimento $numero - " . date('d/m/Y', strtotime($inicio)),
            'inicio' => $inicio,
            'fim' => $fim,
            'dados' => $dadosProcessados
        ];
    }
}