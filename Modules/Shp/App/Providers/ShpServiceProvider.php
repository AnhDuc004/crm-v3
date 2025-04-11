<?php

namespace Modules\Shp\App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Modules\Shp\Repositories\ShpAttributes\ShpAttributeInterface;
use Modules\Shp\Repositories\ShpAttributes\ShpAttributeRepository;
use Modules\Shp\Repositories\ShpBrand\ShpBrandInterface;
use Modules\Shp\Repositories\ShpBrand\ShpBrandRepository;
use Modules\Shp\Repositories\ShpCategory\ShpCategoryInterface;
use Modules\Shp\Repositories\ShpCategory\ShpCategoryRepository;
use Modules\Shp\Repositories\ShpDimensions\ShpDimensionInterface;
use Modules\Shp\Repositories\ShpDimensions\ShpDimensionRepository;
use Modules\Shp\Repositories\ShpGtin\ShpGtinInterface;
use Modules\Shp\Repositories\ShpGtin\ShpGtinRepository;
use Modules\Shp\Repositories\ShpImage\ShpImageInterface;
use Modules\Shp\Repositories\ShpImage\ShpImageRepository;
use Modules\Shp\Repositories\ShpLogistics\ShpLogisticInterface;
use Modules\Shp\Repositories\ShpLogistics\ShpLogisticRepository;
use Modules\Shp\Repositories\ShpPreorder\ShpPreorderInterface;
use Modules\Shp\Repositories\ShpPreorder\ShpPreorderRepository;
use Modules\Shp\Repositories\ShpProduct\ShpProductInterface;
use Modules\Shp\Repositories\ShpProduct\ShpProductRepository;
use Modules\Shp\Repositories\ShpSellerStocks\ShpSellerStockInterface;
use Modules\Shp\Repositories\ShpSellerStocks\ShpSellerStockRepository;
use Modules\Shp\Repositories\ShpTaxInfo\ShpTaxInfoInterface;
use Modules\Shp\Repositories\ShpTaxInfo\ShpTaxInfoRepository;
use Modules\Shp\Repositories\ShpVideo\ShpVideoInterface;
use Modules\Shp\Repositories\ShpVideo\ShpVideoRepository;
use Modules\Shp\Repositories\ShpWholesale\ShpWholesaleInterface;
use Modules\Shp\Repositories\ShpWholesale\ShpWholesaleRepository;

class ShpServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'Shp';

    protected string $moduleNameLower = 'shp';

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
        $this->app->bind(ShpBrandInterface::class, ShpBrandRepository::class);
        $this->app->bind(ShpProductInterface::class, ShpProductRepository::class);
        $this->app->bind(ShpCategoryInterface::class, ShpCategoryRepository::class);
        $this->app->bind(ShpAttributeInterface::class, ShpAttributeRepository::class);
        $this->app->bind(ShpDimensionInterface::class, ShpDimensionRepository::class);
        $this->app->bind(ShpGtinInterface::class, ShpGtinRepository::class);
        $this->app->bind(ShpImageInterface::class, ShpImageRepository::class);
        $this->app->bind(ShpLogisticInterface::class, ShpLogisticRepository::class);
        $this->app->bind(ShpPreorderInterface::class, ShpPreorderRepository::class);
        $this->app->bind(ShpSellerStockInterface::class, ShpSellerStockRepository::class);
        $this->app->bind(ShpTaxInfoInterface::class, ShpTaxInfoRepository::class);
        $this->app->bind(ShpVideoInterface::class, ShpVideoRepository::class);
        $this->app->bind(ShpWholesaleInterface::class, ShpWholesaleRepository::class);
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
