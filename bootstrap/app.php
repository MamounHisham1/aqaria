<?php

use App\Http\Middleware\EnsureIsAdmin;
use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\SetVisitorId;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            require __DIR__.'/../routes/telegram.php';
        },
    )
    ->withSchedule(function (Schedule $schedule): void {
        // Notify users about new listings matching their saved searches.
        $schedule->command('app:send-saved-search-alerts')->dailyAt('08:00');
    })
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias(['admin' => EnsureIsAdmin::class]);

        $middleware->encryptCookies(except: ['appearance', 'sidebar_state', 'visitor_id']);

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
            SetVisitorId::class,
        ]);

        $middleware->preventRequestsDuringMaintenance(except: [
            '/telegram/webhook',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
