<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
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
        Carbon::setLocale('id');

        View::composer('partials.sidebar', function ($view): void {
            $staffList = collect();
            $activeStaff = null;

            try {
                if (Schema::hasTable('users')) {
                    $staffList = User::where('is_active', true)->orderBy('id')->get();
                    $staffId = session('active_staff_id');

                    $activeStaff = $staffId
                        ? $staffList->firstWhere('id', (int) $staffId)
                        : null;

                    $activeStaff ??= $staffList->firstWhere('email', 'kasir@kafe.test')
                        ?? $staffList->first();
                }
            } catch (\Throwable) {
                // Safe fallback if database is not migrated yet.
            }

            $view->with([
                'staffList' => $staffList,
                'activeStaff' => $activeStaff,
            ]);
        });
    }
}
