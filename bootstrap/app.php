<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Configuration\Commands;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        channels: __DIR__ . '/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
            \App\Http\Middleware\PersistSidebarSelection::class,
        ]);

        // تسجيل middleware للموظفين والمهندسين
        $middleware->alias([
            'employee.auth' => \App\Http\Middleware\EmployeeAuth::class,
            'engineer.access' => \Modules\Inquiries\Middleware\EngineerAccessMiddleware::class,
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);

        // تسجيل middleware groups
        $middleware->group('employee', [
            \App\Http\Middleware\EmployeeAuth::class,
        ]);
    })->withCommands([
        \Modules\Inquiries\Console\TestGoogleMapsCommand::class,
    ])
    ->withExceptions(function (Exceptions $exceptions) {
        // معالجة أخطاء Phiki Pattern Search
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
