<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
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
        if (app()->environment('production') || request()->header('x-forwarded-proto') === 'https' || str_starts_with(config('app.url'), 'https://')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        Gate::policy(\App\Models\Kelas::class, \App\Policies\KelasPolicy::class);
        Gate::policy(\App\Models\Kamar::class, \App\Policies\KamarPolicy::class);
        Gate::policy(\App\Models\PenempatanKamar::class, \App\Policies\PenempatanKamarPolicy::class);
        Gate::policy(\App\Models\Perizinan::class, \App\Policies\PerizinanPolicy::class);
        Gate::policy(\App\Models\Pelanggaran::class, \App\Policies\PelanggaranPolicy::class);
        Gate::policy(\App\Models\User::class, \App\Policies\UserPolicy::class);
    }
}
