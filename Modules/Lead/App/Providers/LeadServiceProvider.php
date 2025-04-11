<?php

namespace Modules\Lead\App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Modules\Lead\Repositories\Lead\LeadSourceInterface;
use Modules\Lead\Repositories\Lead\LeadSourceRepository;
use Modules\Lead\Repositories\Lead\LeadStatusInterface;
use Modules\Lead\Repositories\Lead\LeadStatusRepository;
use Modules\Lead\Repositories\Lead\LeadInterface;
use Modules\Lead\Repositories\Lead\LeadRepository;
use Modules\Lead\Repositories\Lead\LeadActivityLogInterface;
use Modules\Lead\Repositories\Lead\LeadActivityLogRepository;
use Modules\Lead\Repositories\Lead\LeadIntegrationEmailInterface;
use Modules\Lead\Repositories\Lead\LeadIntegrationEmailRepository;
use Modules\Lead\Repositories\Lead\LeadEmailIntegrationInterface;
use Modules\Lead\Repositories\Lead\LeadEmailIntegrationRepository;
use Modules\Lead\Repositories\WebToLead\WebToLeadInterface;
use Modules\Lead\Repositories\WebToLead\WebToLeadRepository;


class LeadServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'Lead';

    protected string $moduleNameLower = 'lead';

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
        $this->app->singleton(LeadSourceInterface::class, LeadSourceRepository::class);
        $this->app->singleton(LeadStatusInterface::class, LeadStatusRepository::class);
        $this->app->singleton(LeadInterface::class, LeadRepository::class);
        $this->app->singleton(LeadActivityLogInterface::class, LeadActivityLogRepository::class);
        $this->app->singleton(LeadIntegrationEmailInterface::class, LeadIntegrationEmailRepository::class);
        $this->app->singleton(LeadEmailIntegrationInterface::class, LeadEmailIntegrationRepository::class);
        $this->app->singleton(WebToLeadInterface::class, WebToLeadRepository::class);
      
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
