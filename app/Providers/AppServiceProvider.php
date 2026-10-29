<?php

namespace App\Providers;

use App\Contracts\LeverancierCatalog;
use App\Database\PdoLeverancierCatalog;
use App\Database\SqliteLeverancierCatalog;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(LeverancierCatalog::class, function (): LeverancierCatalog {
            if (DB::getDriverName() === 'mysql') {
                return new PdoLeverancierCatalog(DB::connection()->getPdo());
            }

            return new SqliteLeverancierCatalog;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define(
            'access-admin-dashboard',
            fn (User $user): bool => $user->hasRole(UserRole::Admin),
        );

        Gate::define(
            'access-medewerker-dashboard',
            fn (User $user): bool => $user->hasRole(
                UserRole::Admin,
                UserRole::MagazijnMedewerker,
            ),
        );
    }
}
