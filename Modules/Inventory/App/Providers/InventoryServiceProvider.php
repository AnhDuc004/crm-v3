<?php

namespace Modules\Inventory\App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Modules\Inventory\Repositories\InventoryCheckReport\InventoryCheckReportInterface;
use Modules\Inventory\Repositories\InventoryCheckReport\InventoryCheckReportRepository;
use Modules\Inventory\Repositories\InventoryTransaction\InventoryTransactionInterface;
use Modules\Inventory\Repositories\InventoryTransaction\InventoryTransactionRepository;
use Modules\Inventory\Repositories\Material\MaterialInterface;
use Modules\Inventory\Repositories\Material\MaterialRepository;
use Modules\Inventory\Repositories\Product\ProductInterface;
use Modules\Inventory\Repositories\Product\ProductRepository;
use Modules\Inventory\Repositories\ProductionNorm\ProductionNormInterface;
use Modules\Inventory\Repositories\ProductionNorm\ProductionNormRepository;
use Modules\Inventory\Repositories\SalesOrder\SalesOrderInterface;
use Modules\Inventory\Repositories\SalesOrder\SalesOrderRepository;
use Modules\Inventory\Repositories\SalesOrderItem\SalesOrderItemInterface;
use Modules\Inventory\Repositories\SalesOrderItem\SalesOrderItemRepository;
use Modules\Inventory\Repositories\StockReport\StockReportInterface;
use Modules\Inventory\Repositories\StockReport\StockReportRepository;
use Modules\Inventory\Repositories\Supplier\SupplierInterface;
use Modules\Inventory\Repositories\Supplier\SupplierRepository;
use Modules\Inventory\Repositories\Unit\UnitInterface;
use Modules\Inventory\Repositories\Unit\UnitRespository;
use Modules\Inventory\Repositories\Warehouse\WarehouseInterface;
use Modules\Inventory\Repositories\Warehouse\WarehouseRepository;

class InventoryServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'Inventory';

    protected string $moduleNameLower = 'inventory';

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
        $this->app->bind(UnitInterface::class, UnitRespository::class);
        $this->app->bind(SupplierInterface::class, SupplierRepository::class);
        $this->app->bind(MaterialInterface::class, MaterialRepository::class);
        $this->app->bind(ProductInterface::class, ProductRepository::class);
        $this->app->bind(ProductionNormInterface::class, ProductionNormRepository::class);
        $this->app->bind(WarehouseInterface::class, WarehouseRepository::class);
        $this->app->bind(InventoryTransactionInterface::class, InventoryTransactionRepository::class);
        $this->app->bind(StockReportInterface::class, StockReportRepository::class);
        $this->app->bind(InventoryCheckReportInterface::class, InventoryCheckReportRepository::class);
        $this->app->bind(SalesOrderInterface::class, SalesOrderRepository::class);
        $this->app->bind(SalesOrderItemInterface::class, SalesOrderItemRepository::class);
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
