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
    
    public function getExperimentos()
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
        $dataInicio = null;
        $nomeExperimento = null;
        $processandoExperimento = false;

        foreach ($values as $row) {
            if (!empty($row[0])) { // Linha com data/hora
                if (!$processandoExperimento) {
                    // INÍCIO de um novo experimento
                    $dataInicio = $row[0];
                    $nomeExperimento = $row[3] ?? ''; // Nome da coluna D
                    $processandoExperimento = true;
                    
                    // Adiciona primeira medição
                    if (isset($row[1])) {
                        $experimentoAtual[] = [
                            'tempo' => $row[1],
                            'temperatura' => $row[2] ?? null
                        ];
                    }
                } else {
                    // FIM do experimento atual (nova data/hora)
                    // Adiciona última medição antes de finalizar
                    if (isset($row[1])) {
                        $experimentoAtual[] = [
                            'tempo' => $row[1],
                            'temperatura' => $row[2] ?? null
                        ];
                    }
                    
                    // Registra o experimento completo
                    $experimentos[] = $this->criarExperimento($dataInicio, $row[0], $nomeExperimento, $experimentoAtual);
                    
                    // Reseta para aguardar próximo experimento
                    $processandoExperimento = false;
                    $experimentoAtual = [];
                }
            } elseif ($processandoExperimento) {
                // Continuação do experimento atual (linha sem data/hora)
                if (isset($row[1])) {
                    $experimentoAtual[] = [
                        'tempo' => $row[1],
                        'temperatura' => $row[2] ?? null
                    ];
                }
            }
        }

        // Adiciona o último experimento se estiver em andamento
        if ($processandoExperimento && !empty($experimentoAtual)) {
            $experimentos[] = $this->criarExperimento($dataInicio, null, $nomeExperimento, $experimentoAtual);
        }

        return $experimentos;
    }

    private function criarExperimento($inicio, $fim, $nome, $dados)
    {
        return [
            'id' => md5($inicio.$nome), // ID único
            'nome' => $nome,
            'inicio' => $inicio,
            'fim' => $fim,
            'dados' => array_map(function($medicao) {
                return [
                    'tempo' => str_replace(',', '.', $medicao['tempo']),
                    'temperatura' => str_replace(',', '.', $medicao['temperatura'])
                ];
            }, $dados)
        ];
    }


    private function calcularMetricas($dados)
    {
        $temperaturas = array_column($dados, 'temperatura');
        $temperaturas = array_filter($temperaturas, function($temp) {
            return is_numeric($temp);
        });
        
        if (empty($temperaturas)) {
            return [
                'max' => null,
                'avg' => null
            ];
        }
        
        return [
            'max' => max($temperaturas),
            'avg' => array_sum($temperaturas) / count($temperaturas)
        ];
    }
}