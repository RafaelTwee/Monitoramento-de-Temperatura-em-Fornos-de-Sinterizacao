<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Google_Service_Sheets_BatchUpdateSpreadsheetRequest;
use Google_Service_Sheets_ValueRange;
use Google\Client;
use Google\Service\Sheets;
use Carbon\Carbon;

class SheetsController 
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
        $client->addScope(Sheets::SPREADSHEETS_READONLY);

        $service       = new Sheets($client);
        $spreadsheetId = '15N7ceBWKeWTykIkRHa2vnxB7DJViqtA_s5-ydLPfmxs';
        $range         = 'Experimentos!A2:D';
        $response      = $service->spreadsheets_values->get($spreadsheetId, $range);
        $values        = $response->getValues();

        $experimentos        = [];
        $experimentoAtual    = [];
        $processando         = false;
        $dataInicio          = null;
        $nomeExperimento     = null;
        $headerOffset        = 2;      // dados começam na linha 2 da sheet
        $startRow            = null;   // será definido ao iniciar experimento

        foreach ($values as $idx => $row) {
            $rowNumber = $headerOffset + $idx;  // linha real na planilha (1‐based)

            if (! empty($row[0])) {
                // encontrou data/hora → início ou término de experimento
                if (! $processando) {
                    // ------ INÍCIO ------
                    $processando     = true;
                    $startRow        = $rowNumber;
                    $dataInicio      = $row[0];
                    $nomeExperimento = $row[3] ?? '';

                    // registra primeira medição (se existir)
                    if (isset($row[1])) {
                        $experimentoAtual[] = [
                            'tempo'       => $row[1],
                            'temperatura' => $row[2] ?? null,
                        ];
                    }
                } else {
                    // ------ FIM do experimento atual ------
                    // adiciona última medição (se existir)
                    if (isset($row[1])) {
                        $experimentoAtual[] = [
                            'tempo'       => $row[1],
                            'temperatura' => $row[2] ?? null,
                        ];
                    }

                    $endRow = $rowNumber;

                    // monta array com dados brutos (sem id ainda)
                    $exp = $this->criarExperimento($dataInicio, $row[0], $nomeExperimento, $experimentoAtual);

                    // agora acrescenta startRow, endRow e id estável
                    $exp['startRow'] = $startRow;
                    $exp['endRow']   = $endRow;
                    $exp['id']       = md5($dataInicio . $startRow);

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
                        'temperatura' => $row[2] ?? null,
                    ];
                }
            }
        }

        // se terminou lendo e ainda havia um experimento em aberto
        if ($processando && ! empty($experimentoAtual)) {
            $lastIdx = count($values) - 1;
            $endRow  = $headerOffset + $lastIdx;

            $exp = $this->criarExperimento($dataInicio, null, $nomeExperimento, $experimentoAtual);
            $exp['startRow'] = $startRow;
            $exp['endRow']   = $endRow;
            $exp['id']       = md5($dataInicio . $startRow);
            $experimentos[]  = $exp;
        }

        return $experimentos;
    }

    private function criarExperimento($inicio, $fim, $nome, $dados)
    {
        return [
            // removemos o 'id' daqui, pois ele será gerado depois com startRow
            'nome'   => $nome,
            'inicio' => $inicio,
            'fim'    => $fim,
            'dados'  => array_map(function($medicao) {
                return [
                    'tempo'       => str_replace(',', '.', $medicao['tempo']),
                    'temperatura' => str_replace(',', '.', $medicao['temperatura'])
                ];
            }, $dados),
        ];
    }

    public function updateNome(Request $request, $id)
    {
        // Validação simples: nome obrigatório, no máximo 255 caracteres
        $data = $request->validate([
            'nome' => 'required|string|max:255',
        ]);

        // Carrega TODOS os experimentos e tenta achar o que tem id === $id
        $todos = $this->getExperimentos();
        $toUpdate = collect($todos)->first(fn($exp) => $exp['id'] === $id);

        if (! $toUpdate) {
            return response()->json([
                'message' => 'Experimento não encontrado.'
            ], 404);
        }

        $startRow = $toUpdate['startRow']; // linha em que o nome está na coluna D

        // Inicializa cliente e serviço do Google Sheets
        $client = new Client();

        if ($creds = env('GOOGLE_CREDENTIALS_JSON')) {
            $client->setAuthConfig(json_decode($creds, true));
        } else {
            $client->setAuthConfig(base_path('google-credentials.json'));
        }

        $client->addScope(Sheets::SPREADSHEETS);
        $service = new Sheets($client);

        $spreadsheetId = '15N7ceBWKeWTykIkRHa2vnxB7DJViqtA_s5-ydLPfmxs';

        // Prepara para sobrescrever a coluna D na linha $startRow
        $novoNome = $data['nome'];
        $range = "Experimentos!D{$startRow}:D{$startRow}";

        $valueRange = new Google_Service_Sheets_ValueRange([
            'values' => [
                [ (string) $novoNome ]
            ]
        ]);

        $params = [
            'valueInputOption' => 'USER_ENTERED'
        ];

        try {
            $service->spreadsheets_values->update(
                $spreadsheetId,
                $range,
                $valueRange,
                $params
            );
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Falha ao gravar no Google Sheets: ' . $e->getMessage()
            ], 500);
        }

        return response()->json([
            'status' => 'sucesso',
            'nome'   => $novoNome,
        ]);
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

        $googleCredentials = env('GOOGLE_CREDENTIALS_JSON');

        if ($googleCredentials) {
            $credentials = json_decode($googleCredentials, true);
            $client->setAuthConfig($credentials);
        } else {
            $client->setAuthConfig(base_path('google-credentials.json'));
        }

        $client->addScope(\Google\Service\Sheets::SPREADSHEETS);
        $service = new \Google\Service\Sheets($client);

        // 4) ID da sua planilha e descoberta do sheetId da aba "Experimentos"
        $spreadsheetId = '15N7ceBWKeWTykIkRHa2vnxB7DJViqtA_s5-ydLPfmxs';
        $meta = $service->spreadsheets->get($spreadsheetId);
        $sheet = collect($meta->getSheets())
                ->first(fn($s) => $s->getProperties()->getTitle() === 'Experimentos');
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

    public function destroyRange(Request $request)
    {
        // 1) Validação das datas
        $data = $request->validate([
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
        ]);

        $startDT = Carbon::parse($data['start_date'])->startOfDay();
        $endDT   = Carbon::parse($data['end_date'])->endOfDay();

        // 2) Filtra experimentos no período
        $experimentos = $this->getExperimentos();
        $toDelete = [];
        foreach ($experimentos as $exp) {
            $isoInicio = $this->formatarDataParaISO($exp['inicio']);
            $dtInicio  = Carbon::parse($isoInicio);
            if ($dtInicio->between($startDT, $endDT)) {
                $toDelete[] = $exp;
            }
        }

        if (empty($toDelete)) {
            return redirect()->route('welcome')
                             ->with('error', 'Nenhum experimento encontrado neste período.');
        }

        // 3) Inicializa Sheets API
        $client = new Client();
        if ($creds = env('GOOGLE_CREDENTIALS_JSON')) {
            $client->setAuthConfig(json_decode($creds, true));
        } else {
            $client->setAuthConfig(base_path('google-credentials.json'));
        }
        $client->addScope(\Google\Service\Sheets::SPREADSHEETS);
        $service       = new Sheets($client);
        $spreadsheetId = '15N7ceBWKeWTykIkRHa2vnxB7DJViqtA_s5-ydLPfmxs';

        // 4) Descobre o sheetId da aba "Experimentos"
        $meta  = $service->spreadsheets->get($spreadsheetId);
        $sheet = collect($meta->getSheets())
            ->first(fn($s) => $s->getProperties()->getTitle() === 'Experimentos');
        $sheetId = $sheet->getProperties()->getSheetId();

        // 5) Monta vários deleteDimension (em ordem decrescente de startRow)
        usort($toDelete, fn($a, $b) => $b['startRow'] <=> $a['startRow']);
        $requests = [];
        foreach ($toDelete as $exp) {
            $requests[] = [
                'deleteDimension' => [
                    'range' => [
                        'sheetId'    => $sheetId,
                        'dimension'  => 'ROWS',
                        'startIndex' => $exp['startRow'] - 1,
                        'endIndex'   => $exp['endRow'],
                    ]
                ]
            ];
        }

        // 6) Executa o batchUpdate para remover todas as faixas de uma vez
        $batch = new Google_Service_Sheets_BatchUpdateSpreadsheetRequest([
            'requests' => $requests,
        ]);
        $service->spreadsheets->batchUpdate($spreadsheetId, $batch);

        return redirect()->route('welcome')->with('success', 'Experimentos excluídos com sucesso.');
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