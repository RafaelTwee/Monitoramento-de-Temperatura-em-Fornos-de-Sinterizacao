<?php


namespace App\Http\Controllers;

use App\Services\GoogleSheetService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class UserController extends Controller
{
    public function index(GoogleSheetService $sheet)
    {
        // lê do config em vez de env()
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
                'password' => $row[1] ?? '',         // <— aqui
                'isAdmin'  => filter_var($row[2] ?? '', FILTER_VALIDATE_BOOLEAN),
            ]);
        }
    }

        abort(404, 'Usuário não encontrado.');
    }

    // recebe a submissão e grava no Google Sheets
    public function update(Request $request, string $name, GoogleSheetService $sheet)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'password' => 'nullable|string|min:4',
            'isAdmin'  => 'sometimes|boolean',
        ]);

        // captura true/false corretamente mesmo sem isAdmin no request
        $isAdmin = $request->boolean('isAdmin');

        $sheetId = Config::get('services.googlesheets.spreadsheet_id');
        $row     = $request->rowIndex;
        $range   = "Usuarios!A{$row}:C{$row}";

        // só altera senha se preenchida
        $senha = $data['password'] !== null && $data['password'] !== ''
            ? $data['password']
            : '';

        $values = [[
            $data['name'],
            $senha,
            $isAdmin ? 'TRUE' : 'FALSE',
        ]];

        $sheet->updateSheetValues($sheetId, $range, $values);

        return redirect()->route('users.index')
                        ->with('success', 'Usuário atualizado.');
    }


    public function destroy(string $name, GoogleSheetService $sheet)
    {
        // 1) encontra a linha do usuário
        $sheetId = config('services.googlesheets.spreadsheet_id');
        $rows    = $sheet->getSheetValues($sheetId, 'Usuarios!A2:C');
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

        // 2) “limpa” a linha inteira (ou use o ClearValuesRequest para remover)
        $range = "Usuarios!A{$rowIndex}:C{$rowIndex}";
        $empty  = [['', '', '']];
        $sheet->updateSheetValues($sheetId, $range, $empty);

        return redirect()->route('users.index')
                        ->with('success', "Usuário “{$name}” removido.");
    }


    public function create()
    {
        return view('users.create');
    } 
    

    public function store(Request $request, GoogleSheetService $sheet)
    {
        // 1) Validação básica
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'password' => 'required|string|min:4',
            'isAdmin'  => 'nullable|boolean',
        ]);

        // 2) Carrega toda a coluna de nomes
        $spreadsheetId = config('services.googlesheets.spreadsheet_id');
        $rows = $sheet->getSheetValues($spreadsheetId, 'Usuarios!A2:C');
        $names = array_column($rows, 0);

        // 3) Checa unicidade
        if (in_array($data['name'], $names)) {
            return back()
                ->withInput()
                ->withErrors(['name' => 'Já existe um usuário com este nome.']);
        }

        // 4) Se estiver livre, insere ao final da planilha
        $nextRow = count($rows) + 2; // +2 porque começamos em A2
        $range   = "Usuarios!A{$nextRow}:C{$nextRow}";
        $senha   = $data['password'];
        $isAdmin = $data['isAdmin'] ? 'TRUE' : 'FALSE';
        $values  = [[$data['name'], $senha, $isAdmin]];
        $sheet->updateSheetValues($spreadsheetId, $range, $values);

        return redirect()->route('users.index')
                        ->with('success', 'Usuário criado com sucesso.');
    }
}
