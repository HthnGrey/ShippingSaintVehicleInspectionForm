<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

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
        Gate::before(fn (User $user) => $user->isAdmin() ? true : null);

        Gate::define('view-dashboard', fn (User $user) => $user->hasAnyRole(User::ROLES));
        Gate::define('manage-users', fn (User $user) => $user->isAdmin());
        Gate::define('view-vehicles', fn (User $user) => $user->isManager());
        Gate::define('create-vehicles', fn (User $user) => $user->isManager());
        Gate::define('update-vehicles', fn (User $user) => $user->isManager());
        Gate::define('delete-vehicles', fn (User $user) => $user->isManager());
        Gate::define('manage-work-orders', fn (User $user) => $user->isManager());
        Gate::define('view-driver-mileage', fn (User $user) => $user->isManager());
        Gate::define('view-driver-history', fn (User $user) => $user->hasAnyRole(User::ROLES));
        Gate::define('view-audit-logs', fn (User $user) => $user->isAdmin());
        Gate::define('view-inspections', fn (User $user) => $user->isAdmin());
        Gate::define('create-inspections', fn (User $user) => $user->hasAnyRole([User::ROLE_MANAGER, User::ROLE_DRIVER]));
        Gate::define('manage-inspections', fn (User $user) => $user->isAdmin());
        Gate::define('view-reports', fn (User $user) => $user->isManager());
        Gate::define('manage-reports', fn (User $user) => $user->isAdmin());

        Vite::prefetch(concurrency: 3);
    }
}
