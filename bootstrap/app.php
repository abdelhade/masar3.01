<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Configuration\Commands;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        channels: __DIR__ . '/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // ✅ Global Tenancy Middleware - يطبق على جميع web routes في كل الـ modules
        $tenancyMiddleware = array_filter([
            class_exists(\Stancl\Tenancy\Middleware\InitializeTenancyBySubdomain::class)
                ? \Stancl\Tenancy\Middleware\InitializeTenancyBySubdomain::class
                : null,
            class_exists(\Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains::class)
                ? \Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains::class
                : null,
        ]);

        $middleware->web(append: array_merge(
            $tenancyMiddleware,
            [
                \App\Http\Middleware\SetLocale::class,
                \App\Http\Middleware\PersistSidebarSelection::class,
            ]
        ));

        // ✅ Global Tenancy Middleware - يطبق على جميع api routes في كل الـ modules
        $middleware->api(append: $tenancyMiddleware);


        $middleware->alias([
            'employee.auth' => \Modules\HR\Http\Middleware\EmployeeAuth::class,
            'engineer.access' => \Modules\Inquiries\Middleware\EngineerAccessMiddleware::class,
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);


        $middleware->group('employee', [
            \Modules\HR\Http\Middleware\EmployeeAuth::class,
        ]);
    })->withCommands([
        \Modules\Inquiries\Console\TestGoogleMapsCommand::class,
    ])
    ->withExceptions(function (Exceptions $exceptions) {

        $exceptions->render(function (Throwable $e, $request) {
            if (
                str_contains($e->getMessage(), 'FailedToInitializePatternSearchException') ||
                str_contains($e->getMessage(), 'PatternSearcher') ||
                str_contains($e->getMessage(), 'syntax-highlight')
            ) {
                \Illuminate\Support\Facades\Log::error('Phiki Pattern Search Error: ' . $e->getMessage());

                // إرجاع صفحة خطأ بسيطة
                return response()->view('errors.simple', [
                    'message' => 'حدث خطأ في البحث. يرجى المحاولة مرة أخرى.',
                ], 500);
            }

            return null;
        });
    })->create();
