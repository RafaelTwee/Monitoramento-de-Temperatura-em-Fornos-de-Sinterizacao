<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * O “home” para onde o usuário é redirecionado após o login.
     */
    public const HOME = '/home';

    /**
     * Defina aqui suas rate limits, se usar APIs.
     */
    protected function configureRateLimiting(): void
    {
        // exemplo para API:
        // RateLimiter::for('api', function (Request $request) {
        //     return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        // });
    }

    /**
     * Boot the provider.
     */
    public function boot(): void
    {
        parent::boot();

        $this->routes(function () {
            // Rotas de API (se existirem)
            Route::prefix('api')
                 ->middleware('api')
                 ->group(base_path('routes/api.php'));

            // Rotas web
            Route::middleware('web')
                 ->group(base_path('routes/web.php'));
        });
    }
}
