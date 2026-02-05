<?php

namespace Modules\Quality\Providers;

use Illuminate\Support\ServiceProvider;

class QualityServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'Quality';

    public function boot(): void
    {
        $this->loadJsonTranslationsFrom(__DIR__ . '/../resources/lang');
        $this->registerViews();
        $this->loadMigrationsFrom(module_path($this->moduleName, 'database/migrations'));
    }

    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);
    }

    protected function registerViews(): void
    {
        $viewPath = resource_path('views/modules/' . strtolower($this->moduleName));
        $sourcePath = module_path($this->moduleName, 'resources/views');

        $this->publishes([
            $sourcePath => $viewPath
        ], ['views', $this->moduleName . '-module-views']);

        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths(), [$sourcePath]), strtolower($this->moduleName));
    }

    private function getPublishableViewPaths(): array
    {
        $paths = [];
        foreach ($this->app['config']->get('view.paths') as $path) {
            if (is_dir($path . '/modules/' . strtolower($this->moduleName))) {
                $paths[] = $path . '/modules/' . strtolower($this->moduleName);
            }
        }

        return $paths;
    }
}

