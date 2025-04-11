<?php

namespace Modules\Campaign\App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

use Modules\Campaign\Repositories\Campaign\CampaignInterface;
use Modules\Campaign\Repositories\Campaign\CampaignRepository;
use Modules\Campaign\Repositories\Campaign\CampaignContentInterface;
use Modules\Campaign\Repositories\Campaign\CampaignContentRepository;
use Modules\Campaign\Repositories\Campaign\CampaignExeInterface;
use Modules\Campaign\Repositories\Campaign\CampaignExeRepository;
use Modules\Campaign\Repositories\Campaign\CampaignGroupInterface;
use Modules\Campaign\Repositories\Campaign\CampaignGroupRepository;
use Modules\Campaign\Repositories\Campaign\CampaignImageInterface;
use Modules\Campaign\Repositories\Campaign\CampaignImageRepository;
use Modules\Campaign\Repositories\Domain\DomainInterface;
use Modules\Campaign\Repositories\Domain\DomainRepository;
use Modules\Campaign\Repositories\Domain\FbGroupInterface;
use Modules\Campaign\Repositories\Domain\FbGroupRepository;
use Modules\Campaign\Repositories\Domain\GroupDomainInterface;
use Modules\Campaign\Repositories\Domain\GroupDomainRepository;

class CampaignServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'Campaign';

    protected string $moduleNameLower = 'campaign';

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
        $this->app->singleton(CampaignInterface::class, CampaignRepository::class);
        $this->app->singleton(CampaignContentInterface::class, CampaignContentRepository::class);
        $this->app->singleton(CampaignExeInterface::class, CampaignExeRepository::class);
        $this->app->singleton(CampaignGroupInterface::class, CampaignGroupRepository::class);
        $this->app->singleton(CampaignImageInterface::class, CampaignImageRepository::class);
        $this->app->singleton(DomainInterface::class, DomainRepository::class);
        $this->app->singleton(FbGroupInterface::class, FbGroupRepository::class);
        $this->app->singleton(GroupDomainInterface::class, GroupDomainRepository::class);
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
        $langPath = resource_path('lang/modules/'.$this->moduleNameLower);

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
        $this->publishes([module_path($this->moduleName, 'config/config.php') => config_path($this->moduleNameLower.'.php')], 'config');
        $this->mergeConfigFrom(module_path($this->moduleName, 'config/config.php'), $this->moduleNameLower);
    }

    /**
     * Register views.
     */
    public function registerViews(): void
    {
        $viewPath = resource_path('views/modules/'.$this->moduleNameLower);
        $sourcePath = module_path($this->moduleName, 'resources/views');

        $this->publishes([$sourcePath => $viewPath], ['views', $this->moduleNameLower.'-module-views']);

        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths(), [$sourcePath]), $this->moduleNameLower);

        $componentNamespace = str_replace('/', '\\', config('modules.namespace').'\\'.$this->moduleName.'\\'.config('modules.paths.generator.component-class.path'));
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
            if (is_dir($path.'/modules/'.$this->moduleNameLower)) {
                $paths[] = $path.'/modules/'.$this->moduleNameLower;
            }
        }

        return $paths;
    }
}
