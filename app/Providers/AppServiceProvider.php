<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Carbon::setLocale(config('app.locale'));

        // Admin memiliki akses penuh; policy tetap menjadi lapisan kedua.
        Gate::before(function (User $user, string $ability) {
            return $user->isAdmin() ? true : null;
        });
    }
}
