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
        
        $googleCredentials = env('GOOGLE_CREDENTIALS_JSON');

        if ($googleCredentials) {
            $credentials = json_decode($googleCredentials, true);
        } else {
            $credentials = base_path('google-credentials.json');
        }

        $client->setAuthConfig($credentials);

        
        $client->setAuthConfig($credentials);
        $client->addScope(Sheets::SPREADSHEETS_READONLY);

        $service       = new Sheets($client);
        $spreadsheetId = '15N7ceBWKeWTykIkRHa2vnxB7DJViqtA_s5-ydLPfmxs';
        $range         = 'Página1!A2:D';
        $response      = $service->spreadsheets_values->get($spreadsheetId, $range);
        $values        = $response->getValues();

        $experimentos        = [];
        $experimentoAtual    = [];
        $processando         = false;
        $dataInicio          = null;
        $nomeExperimento     = null;
        $headerOffset        = 2;                   // dados começam na linha 2 da sheet
        $startRow            = null;                // será definido ao iniciar experimento

        foreach ($values as $idx => $row) {
            $rowNumber = $headerOffset + $idx;     // linha real na planilha

            if (! empty($row[0])) {
                // encontrou data/hora → início ou término de experimento
                if (! $processando) {
                    // INÍCIO
                    $processando      = true;
                    $startRow         = $rowNumber;
                    $dataInicio       = $row[0];
                    $nomeExperimento  = $row[3] ?? '';

                    // registra primeira medição
                    if (isset($row[1])) {
                        $experimentoAtual[] = [
                            'tempo'        => $row[1],
                            'temperatura'  => $row[2] ?? null
                        ];
                    }
                } else {
                    // FIM do experimento atual
                    // adiciona última medição
                    if (isset($row[1])) {
                        $experimentoAtual[] = [
                            'tempo'       => $row[1],
                            'temperatura' => $row[2] ?? null
                        ];
                    }

                    $endRow = $rowNumber;

                    // monta o array completo e anexa start/end
                    $exp = $this->criarExperimento($dataInicio, $row[0], $nomeExperimento, $experimentoAtual);
                    $exp['startRow'] = $startRow;
                    $exp['endRow']   = $endRow;
                    $experimentos[]  = $exp;

                    // reseta para o próximo
                    $processando      = false;
                    $experimentoAtual = [];
                }
            } elseif ($processando) {
                // linhas intermediárias do experimento
                if (isset($row[1])) {
                    $experimentoAtual[] = [
                        'tempo'       => $row[1],
                        'temperatura' => $row[2] ?? null
                    ];
                }
            }
        }

        // se terminou lendo e ainda havia um experimento em aberto:
        if ($processando && ! empty($experimentoAtual)) {
            $lastIdx = count($values) - 1;
            $endRow  = $headerOffset + $lastIdx;

            $exp = $this->criarExperimento($dataInicio, null, $nomeExperimento, $experimentoAtual);
            $exp['startRow'] = $startRow;
            $exp['endRow']   = $endRow;
            $experimentos[]  = $exp;
        }

        return $experimentos;
    }


   private function criarExperimento($inicio, $fim, $nome, $dados)
    {
        return [
            'id' => md5($inicio.$nome),
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

    
    public function destroy($id)
    {
        // 1) Recupera e localiza o experimento
        $experimentos = $this->getExperimentos();
        $toDelete = collect($experimentos)->first(fn($exp) => $exp['id'] === $id);

        if (! $toDelete) {
            return redirect()->route('welcome')
                            ->with('error', 'Experimento não encontrado.');
        }

        // 2) Converte linhas 1-based em índices 0-based para o deleteDimension
        $startRow = $toDelete['startRow'];      // ex: 5
        $endRow   = $toDelete['endRow'] ?? $startRow; // ex: 10
        $startIdx = $startRow - 1;              // ex: 4 (inclusivo)
        $endIdx   = $endRow;                    // ex: 10 (exclusivo)

        // 3) Inicializa cliente e serviço
        $client = new \Google\Client();
        $client->setAuthConfig(base_path('google-credentials.json'));
        $client->addScope(\Google\Service\Sheets::SPREADSHEETS);
        $service = new \Google\Service\Sheets($client);

        // 4) ID da sua planilha e descoberta do sheetId da aba "Página1"
        $spreadsheetId = '15N7ceBWKeWTykIkRHa2vnxB7DJViqtA_s5-ydLPfmxs';
        $meta = $service->spreadsheets->get($spreadsheetId);
        $sheet = collect($meta->getSheets())
                ->first(fn($s) => $s->getProperties()->getTitle() === 'Página1');
        $sheetId = $sheet->getProperties()->getSheetId();

        // 5) Monta e dispara o BatchUpdate para deletar as linhas
        $batchRequest = new \Google_Service_Sheets_BatchUpdateSpreadsheetRequest([
            'requests' => [
                [
                    'deleteDimension' => [
                        'range' => [
                            'sheetId'    => $sheetId,
                            'dimension'  => 'ROWS',
                            'startIndex' => $startIdx,
                            'endIndex'   => $endIdx,
                        ]
                    ]
                ]
            ]
        ]);
        $service->spreadsheets->batchUpdate($spreadsheetId, $batchRequest);

        return redirect()->route('welcome')
                        ->with('success', 'Linhas do experimento excluídas com sucesso.');
    }



    private function formatarDataParaISO($data)
    {
        if (empty($data)) return null;
        
        // Converter de "DD/MM/YYYY_HH:MM:SS" para "YYYY-MM-DD HH:MM:SS"
        $partes = explode('_', $data);
        if (count($partes) !== 2) return $data;
        
        $dataParte = $partes[0];
        $horaParte = $partes[1];
        
        $dataPartes = explode('/', $dataParte);
        if (count($dataPartes) !== 3) return $data;
        
        return sprintf('%s-%s-%s %s', 
            $dataPartes[2], // Ano
            $dataPartes[1], // Mês
            $dataPartes[0], // Dia
            $horaParte     // Hora
        );
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