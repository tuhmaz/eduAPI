<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    public const HOME = '/dashboard';

    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        $this->routes(function () {
            Route::middleware(['api', 'check.api'])
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });

        // Register middlewares
        Route::aliasMiddleware('check.api', \App\Http\Middleware\CheckApiAccess::class);
        Route::aliasMiddleware('track.visitor', \App\Http\Middleware\TrackVisitor::class);

        // Apply TrackVisitor middleware to all web routes
        Route::pushMiddlewareToGroup('web', \App\Http\Middleware\TrackVisitor::class);
    }
}
