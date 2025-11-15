<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Livewire\Volt\Volt;
use Nwidart\Modules\Facades\Module;

class VoltServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $paths = [
            config('livewire.view_path', resource_path('views/livewire')),
            resource_path('views/pages'),
        ];

        // Mount Livewire Volt paths from all enabled modules
        $modules = Module::allEnabled();
        foreach ($modules as $module) {
            $livewirePath = module_path($module->getName(), 'Resources/views/livewire');
            if (is_dir($livewirePath)) {
                $paths[] = $livewirePath;
            }
        }

        Volt::mount($paths);
    }
}
