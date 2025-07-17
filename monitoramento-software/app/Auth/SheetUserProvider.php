<?php
namespace App\Auth;

use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Hashing\Hasher as HasherContract;
use App\Services\GoogleSheetService;
use Illuminate\Support\Facades\Config;

class SheetUserProvider implements UserProvider
{
    protected GoogleSheetService $sheet;
    protected HasherContract      $hasher;
    protected string              $spreadsheetId;
    protected string              $range;

    public function __construct(GoogleSheetService $sheet, HasherContract $hasher)
    {
        $this->sheet  = $sheet;
        $this->hasher = $hasher;

        // Lê do config, que por sua vez veio do .env
        $this->spreadsheetId = Config::get('services.googlesheets.spreadsheet_id', '');

        if ($this->spreadsheetId === '') {
            throw new \InvalidArgumentException(
                'Defina o GOOGLE_SPREADSHEET_ID no seu .env e execute php artisan config:cache'
            );
        }

        $this->range = 'Usuarios!A2:C';
    }

    public function retrieveById($identifier): ?Authenticatable
    {
        // Lê todas as linhas da sheet
        $rows = $this->sheet->getSheetValues($this->spreadsheetId, $this->range);

        // Procura pelo usuário cujo "name" (coluna A) bate com o identificador da sessão
        foreach ($rows as $row) {
            [$nome, $senha, $adm] = $row + [null, null, null];
            if ($nome === $identifier) {
                return new SheetUser($nome, $senha, $adm);
            }
        }

        return null;
    }

    public function retrieveByToken($identifier, $token): ?Authenticatable
    {
        // se você quisesse “remember me”, implementaria aqui
        return null;
    }

    public function updateRememberToken(Authenticatable $user, $token): void
    {
        // não implementado
    }

    public function retrieveByCredentials(array $credentials): ?Authenticatable
    {
        $rows = $this->sheet->getSheetValues($this->spreadsheetId, $this->range);

        foreach ($rows as $row) {
            [$nome, $senha, $adm] = $row + [null, null, null];
            if ($nome === ($credentials['name'] ?? null)) {
                return new SheetUser($nome, $senha, $adm);
            }
        }

        return null;
    }

    public function validateCredentials(Authenticatable $user, array $credentials): bool
    {
        // $this->hasher é uma instância de Illuminate\Hashing\BcryptHasher
        return $this->hasher->check(
            $credentials['password'], 
            $user->getAuthPassword()
        );
    }// return $this->hasher->check($credentials['password'], $user->getAuthPassword());
    

    public function rehashPasswordIfRequired(Authenticatable $user, array $credentials, bool $force = false): bool
    {
        // Não usamos rehash automático aqui
        return false;
    }
}
