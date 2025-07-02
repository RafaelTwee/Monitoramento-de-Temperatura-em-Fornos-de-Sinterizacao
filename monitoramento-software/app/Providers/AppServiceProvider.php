<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use PhpOffice\PhpSpreadsheet\Settings;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Settings::setTempDir(sys_get_temp_dir());
    }
}
