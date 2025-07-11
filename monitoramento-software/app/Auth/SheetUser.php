<?php
namespace App\Auth;

use Illuminate\Contracts\Auth\Authenticatable;

class SheetUser implements Authenticatable
{
    protected string $name;
    protected string $password;
    protected bool   $isAdmin;

    public function __construct(string $name, string $password, $isAdmin)
    {
        $this->name     = $name;
        $this->password = $password;
        $this->isAdmin  = filter_var($isAdmin, FILTER_VALIDATE_BOOLEAN);
    }

    // Qual campo é o identificador (login)
    public function getAuthIdentifierName(): string
    {
        return 'name';
    }

    // Valor do identificador
    public function getAuthIdentifier(): string
    {
        return $this->name;
    }

    // Qual campo contém a senha (campo “password”)
    public function getAuthPasswordName(): string
    {
        return 'password';
    }

    // Valor da senha
    public function getAuthPassword(): string
    {
        return $this->password;
    }

    // Métodos de “remember me”
    public function getRememberToken(): ?string
    {
        return null;
    }

    public function setRememberToken($value): void
    {
        // não usado
    }

    public function getRememberTokenName(): ?string
    {
        return null;
    }

    // Opcional, getter extra
    public function isAdmin(): bool
    {
        return $this->isAdmin;
    }
}
