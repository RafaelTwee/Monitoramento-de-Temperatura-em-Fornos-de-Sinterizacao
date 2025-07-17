<?php

namespace App\Http\Controllers;

use App\Services\GoogleSheetService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash; // ← import
use Google_Service_Sheets_BatchUpdateSpreadsheetRequest;
use Google_Service_Sheets_ValueRange;
use Google\Client;
use Google\Service\Sheets;

class UserController extends Controller
{
    public function index(GoogleSheetService $sheet)
    {
        $spreadsheetId = Config::get('services.googlesheets.spreadsheet_id');
        $rows = $sheet->getSheetValues($spreadsheetId, 'Usuarios!A2:C');
        $users = array_map(fn($r) => [
            'name'    => $r[0] ?? '',
            'isAdmin' => filter_var($r[2] ?? '', FILTER_VALIDATE_BOOLEAN),
        ], $rows);
        return view('users.index', compact('users'));
    }

    public function edit(string $name, GoogleSheetService $sheet)
    {
        $sheetId = Config::get('services.googlesheets.spreadsheet_id');
        $rows    = $sheet->getSheetValues($sheetId, 'Usuarios!A2:C');

        foreach ($rows as $idx => $row) {
            if (($row[0] ?? '') === $name) {
                return view('users.edit', [
                    'rowIndex' => $idx + 2,
                    'name'     => $row[0],
                    'isAdmin'  => filter_var($row[2] ?? '', FILTER_VALIDATE_BOOLEAN),
                ]);
            }
        }

        abort(404, 'Usuário não encontrado.');
    }

    public function update(Request $request, string $name, GoogleSheetService $sheet)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'password' => 'nullable|string|min:4',
            'isAdmin'  => 'sometimes|boolean',
            'rowIndex' => 'required|integer',
        ]);

        // capturar checkbox
        $isAdmin = $request->boolean('isAdmin');

        $sheetId = Config::get('services.googlesheets.spreadsheet_id');
        $row     = $data['rowIndex'];
        $range   = "Usuarios!A{$row}:C{$row}";

        // só hashiar se vier senha nova
        if ($request->filled('password')) {
            $senhaHashed = Hash::make($data['password']);
        } else {
            $senhaHashed = ''; // não sobrescreve
        }

        $values = [[
            $data['name'],
            $senhaHashed,
            $isAdmin ? 'TRUE' : 'FALSE',
        ]];

        $sheet->updateSheetValues($sheetId, $range, $values);

        return redirect()->route('users.index')
                         ->with('success', 'Usuário atualizado.');
    }

    public function destroy(string $name, GoogleSheetService $sheet)
    {
        // 1) encontra o índice da linha (1‑based)
        $spreadsheetId = config('services.googlesheets.spreadsheet_id');
        $rows = $sheet->getSheetValues($spreadsheetId, 'Usuarios!A2:C');
        $rowIndex = null;
        foreach ($rows as $idx => $row) {
            if (($row[0] ?? '') === $name) {
                $rowIndex = $idx + 2;
                break;
            }
        }
        if (is_null($rowIndex)) {
            return redirect()->route('users.index')
                            ->with('error', 'Usuário não encontrado.');
        }

        // 2) converte pra zero‑based
        $startIdx = $rowIndex - 1;  // inclusivo
        $endIdx   = $rowIndex;      // exclusivo

        // 3) inicializa client/serviço
        $client = new \Google\Client();

        $client->setApplicationName('Google Sheets');

        // aqui pegamos as credenciais do .env ou usamos o arquivo em disco
        if ($credsJson = env('GOOGLE_CREDENTIALS_JSON')) {
            $credentials = json_decode($credsJson, true);
        } else {
            $credentials = base_path('google-credentials.json');
        }

        $client->setAuthConfig($credentials);
        $client->addScope(\Google\Service\Sheets::SPREADSHEETS);

        $service = new \Google\Service\Sheets($client);

        // 4) descobre o sheetId da aba "Usuarios"
        $meta = $service->spreadsheets->get($spreadsheetId, ['fields'=>'sheets.properties']);
        $sheetMeta = collect($meta->getSheets())
                    ->first(fn($s) => $s->getProperties()->getTitle() === 'Usuarios');
        $sheetId = $sheetMeta->getProperties()->getSheetId();

        // 5) monta e dispara o batchUpdate
        $batchRequest = new \Google_Service_Sheets_BatchUpdateSpreadsheetRequest([
            'requests' => [[
                'deleteDimension' => [
                    'range' => [
                        'sheetId'    => $sheetId,
                        'dimension'  => 'ROWS',
                        'startIndex' => $startIdx,
                        'endIndex'   => $endIdx,
                    ]
                ]
            ]]
        ]);
        $service->spreadsheets->batchUpdate($spreadsheetId, $batchRequest);

        return redirect()->route('users.index')
                        ->with('success', "Usuário “{$name}” removido.");
    }

    public function create()
    {
        return view('users.create');
    }

    public function store(Request $request, GoogleSheetService $sheet)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'password' => 'required|string|min:4',
            'isAdmin'  => 'nullable|boolean',
        ]);

        $spreadsheetId = config('services.googlesheets.spreadsheet_id');
        $rows = $sheet->getSheetValues($spreadsheetId, 'Usuarios!A2:C');
        $names = array_column($rows, 0);

        if (in_array($data['name'], $names)) {
            return back()
                ->withInput()
                ->withErrors(['name' => 'Já existe um usuário com este nome.']);
        }

        $nextRow = count($rows) + 2; // CORREÇÃO: usar $nextRow aqui
        $range   = "Usuarios!A{$nextRow}:C{$nextRow}";
        $isAdmin = $request->boolean('isAdmin');
        $senhaHashed = Hash::make($data['password']); // bcrypt

        $values = [[
            $data['name'],
            $senhaHashed,
            $isAdmin ? 'TRUE' : 'FALSE',
        ]];

        $sheet->updateSheetValues($spreadsheetId, $range, $values);

        return redirect()->route('users.index')
                         ->with('success', 'Usuário criado com sucesso.');
    }
}
