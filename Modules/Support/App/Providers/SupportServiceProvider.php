<?php

namespace Modules\Support\App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Modules\Support\Repositories\Ticket\TicketInterface;
use Modules\Support\Repositories\Ticket\TicketRepository;
use Modules\Support\Repositories\TicketsPriority\TicketsPriorityInterface;
use Modules\Support\Repositories\TicketsPriority\TicketsPriorityRepository;
use Modules\Support\Repositories\TicketsStatus\TicketsStatusInterface;
use Modules\Support\Repositories\TicketsStatus\TicketsStatusRepository;
use Modules\Support\Repositories\PredefinedReplies\PredefinedRepliesInterface;
use Modules\Support\Repositories\PredefinedReplies\PredefinedRepliesRepository;
use Modules\Support\Repositories\Service\ServiceInterface;
use Modules\Support\Repositories\Service\ServiceRepository;
use Modules\Support\Repositories\SpamFilter\SpamFilterInterface;
use Modules\Support\Repositories\SpamFilter\SpamFilterRepository;

class SupportServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'Support';

    protected string $moduleNameLower = 'support';

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
        $this->app->singleton(TicketInterface::class, TicketRepository::class);
        $this->app->singleton(TicketsPriorityInterface::class, TicketsPriorityRepository::class);
        $this->app->singleton(TicketsStatusInterface::class, TicketsStatusRepository::class);
        $this->app->singleton(PredefinedRepliesInterface::class, PredefinedRepliesRepository::class);
        $this->app->singleton(ServiceInterface::class, ServiceRepository::class);
        $this->app->singleton(SpamFilterInterface::class, SpamFilterRepository::class);
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
