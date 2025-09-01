<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\User;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Register the 'admin' gate for authorization checks
        Gate::define('admin', function (User $user) {
            // If user is a super admin (from central database and accessing from central domain)
            if ($user->isSuperAdmin()) {
                return true;
            }

            // Add any other admin authorization logic here for tenant users
            // For now, only super admins have admin access
            return false;
        });
    }
}
