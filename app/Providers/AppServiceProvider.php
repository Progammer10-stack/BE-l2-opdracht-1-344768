<?php

namespace App\Providers;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('access-admin-dashboard', function (User $user): bool {
            return $user->hasRole(UserRole::Admin);
        });

        // Magazijnmedewerkers en admins mogen de overzichten en leveringen openen.
        Gate::define('access-medewerker-dashboard', function (User $user): bool {
            return $user->hasRole(UserRole::Admin, UserRole::MagazijnMedewerker);
        });
    }
}
