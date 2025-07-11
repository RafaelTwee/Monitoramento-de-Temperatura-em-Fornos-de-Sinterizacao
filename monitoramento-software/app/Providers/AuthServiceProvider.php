<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Hashing\Hasher;
use App\Auth\SheetUserProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // aqui registramos o provider 'sheets'
        Auth::provider('sheets', function($app, array $config) {
            return new SheetUserProvider(
                $app->make(\App\Services\GoogleSheetService::class),
                $app->make(Hasher::class)
            );
        });
    }
}
