<?php

namespace Modules\Checks\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array<string, array<int, string>>
     */
    protected $listen = [
        \Modules\Checks\Events\CheckCreated::class => [
            \Modules\Checks\Listeners\SendCheckCreatedNotification::class,
        ],
        \Modules\Checks\Events\CheckCleared::class => [
            // يمكن إضافة listeners هنا
        ],
        \Modules\Checks\Events\CheckBounced::class => [
            // يمكن إضافة listeners هنا
        ],
        \Modules\Checks\Events\CheckOverdue::class => [
            \Modules\Checks\Listeners\SendCheckOverdueNotification::class,
        ],
    ];

    /**
     * Indicates if events should be discovered.
     *
     * @var bool
     */
    protected static $shouldDiscoverEvents = true;

    /**
     * Configure the proper event listeners for email verification.
     */
    protected function configureEmailVerification(): void {}
}
