<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Gate;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;
use App\Models\Permission;

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
        Schema::defaultStringLength(191);
        \App\Models\User::observe(\App\Observers\UserObserver::class);

        // RBAC Gates: Dynamic Permission Check
        if (Schema::hasTable('permissions')) {
            try {
                Permission::get()->each(function ($permission) {
                    Gate::define($permission->name, function ($user) use ($permission) {
                        return $user->hasPermission($permission);
                    });
                });
            } catch (\Exception $e) {
                // Silently fail if table is empty or migration hasn't run
            }
        }

        // Backward compatibility for existing hardcoded roles
        Gate::define('access-admin', function ($user) {
            return in_array($user->role, ['admin', 'super_admin']) || $user->roles->count() > 0;
        });

        // Hardened Rate Limiters
        RateLimiter::for('admin', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('admin-sensitive', function (Request $request) {
            return Limit::perMinute(10)->by($request->user()?->id ?: $request->ip());
        });
    }
}
