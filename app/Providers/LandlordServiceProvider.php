<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class LandlordServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Configurar conexión landlord
        $this->app->bind('landlord', function () {
            return \DB::connection('mysql');
        });
    }

    public function boot(): void
    {
        // Las migraciones landlord se ejecutan manualmente
        // php artisan migrate --path=database/migrations/landlord --database=mysql
    }
}
}
