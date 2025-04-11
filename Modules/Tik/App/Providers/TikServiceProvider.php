<?php

namespace Modules\Tik\App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Modules\Tik\Repositories\TikAttribute\TikAttributeInterface;
use Modules\Tik\Repositories\TikAttribute\TikAttributeRepository;
use Modules\Tik\Repositories\TikAttributeValue\TikAttributeValueInterface;
use Modules\Tik\Repositories\TikAttributeValue\TikAttributeValueRepository;
use Modules\Tik\Repositories\TikBrand\TikBrandInterface;
use Modules\Tik\Repositories\TikBrand\TikBrandRepository;
use Modules\Tik\Repositories\TikCategory\TikCategoryInterface;
use Modules\Tik\Repositories\TikCategory\TikCategoryRepository;
use Modules\Tik\Repositories\TikFile\TikFileInterface;
use Modules\Tik\Repositories\TikFile\TikFileRepository;
use Modules\Tik\Repositories\TikProduct\TikProductInterface;
use Modules\Tik\Repositories\TikProduct\TikProductRepository;
use Modules\Tik\Repositories\TikProductCertification\TikProductCertificationInterface;
use Modules\Tik\Repositories\TikProductCertification\TikProductCertificationRepository;
use Modules\Tik\Repositories\TikProductImage\TikProductImageInterface;
use Modules\Tik\Repositories\TikProductImage\TikProductImageRepository;
use Modules\Tik\Repositories\TikProductSalesAttribute\TikProductSalesAttributeInterface;
use Modules\Tik\Repositories\TikProductSalesAttribute\TikProductSalesAttributeRepository;
use Modules\Tik\Repositories\TikSku\TikSkuInterface;
use Modules\Tik\Repositories\TikSku\TikSkuRepository;

class TikServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'Tik';

    protected string $moduleNameLower = 'tik';

    /**
     * Boot the application events.
     */
    public function boot(): void
    {
        $this->registerCommands();
        $this->registerCommandSchedules();
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/migrations'));
    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);
        $this->app->bind(TikAttributeInterface::class, TikAttributeRepository::class);
        $this->app->bind(TikAttributeValueInterface::class, TikAttributeValueRepository::class);
        $this->app->bind(TikBrandInterface::class, TikBrandRepository::class);
        $this->app->bind(TikCategoryInterface::class, TikCategoryRepository::class);
        $this->app->bind(TikFileInterface::class, TikFileRepository::class);
        $this->app->bind(TikProductInterface::class, TikProductRepository::class);
        $this->app->bind(TikProductCertificationInterface::class, TikProductCertificationRepository::class);
        $this->app->bind(TikProductImageInterface::class, TikProductImageRepository::class);
        $this->app->bind(TikProductSalesAttributeInterface::class, TikProductSalesAttributeRepository::class);
        $this->app->bind(TikSkuInterface::class, TikSkuRepository::class);
    }

    /**
     * Register commands in the format of Command::class
     */
    protected function registerCommands(): void
    {
        // $this->commands([]);
    }

    /**
     * Register command Schedules.
     */
    protected function registerCommandSchedules(): void
    {
        // $this->app->booted(function () {
        //     $schedule = $this->app->make(Schedule::class);
        //     $schedule->command('inspire')->hourly();
        // });
    }

    /**
     * Register translations.
     */
    public function registerTranslations(): void
    {
        $langPath = resource_path('lang/modules/' . $this->moduleNameLower);

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $this->moduleNameLower);
            $this->loadJsonTranslationsFrom($langPath);
        } else {
            $this->loadTranslationsFrom(module_path($this->moduleName, 'lang'), $this->moduleNameLower);
            $this->loadJsonTranslationsFrom(module_path($this->moduleName, 'lang'));
        }
    }

    /**
     * Register config.
     */
    protected function registerConfig(): void
    {
        $this->publishes([module_path($this->moduleName, 'config/config.php') => config_path($this->moduleNameLower . '.php')], 'config');
        $this->mergeConfigFrom(module_path($this->moduleName, 'config/config.php'), $this->moduleNameLower);
    }

    /**
     * Register views.
     */
    public function registerViews(): void
    {
        $viewPath = resource_path('views/modules/' . $this->moduleNameLower);
        $sourcePath = module_path($this->moduleName, 'resources/views');

        $this->publishes([$sourcePath => $viewPath], ['views', $this->moduleNameLower . '-module-views']);

        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths(), [$sourcePath]), $this->moduleNameLower);

        $componentNamespace = str_replace('/', '\\', config('modules.namespace') . '\\' . $this->moduleName . '\\' . config('modules.paths.generator.component-class.path'));
        Blade::componentNamespace($componentNamespace, $this->moduleNameLower);
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [];
    }

    private function getPublishableViewPaths(): array
    {
        $paths = [];
        foreach (config('view.paths') as $path) {
            if (is_dir($path . '/modules/' . $this->moduleNameLower)) {
                $paths[] = $path . '/modules/' . $this->moduleNameLower;
            }
        }

        return $paths;
    }
}
