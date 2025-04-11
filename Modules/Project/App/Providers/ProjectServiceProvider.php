<?php

namespace Modules\Project\App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Modules\Project\Repositories\Project\ProjectInterface;
use Modules\Project\Repositories\Project\ProjectRepository;
use Modules\Project\Repositories\ProjectFiles\ProjectFilesInterface;
use Modules\Project\Repositories\ProjectFiles\ProjectFilesRepository;
use Modules\Project\Repositories\ProjectDiscussions\ProjectDiscussionsInterface;
use Modules\Project\Repositories\ProjectDiscussions\ProjectDiscussionsRepository;
use Modules\Project\Repositories\ProjectMilestone\ProjectMilestoneInterface;
use Modules\Project\Repositories\ProjectMilestone\ProjectMilestoneRepository;
use Modules\Project\Repositories\ProjectTickets\ProjectTicketsInterface;
use Modules\Project\Repositories\ProjectTickets\ProjectTicketsRepository;
use Modules\Project\Repositories\ProjectActivity\ProjectActivityInterface;
use Modules\Project\Repositories\ProjectActivity\ProjectActivityRepository;
use Modules\Project\Repositories\ProjectNotes\ProjectNotesInterface;
use Modules\Project\Repositories\ProjectNotes\ProjectNotesRepository;


class ProjectServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'Project';

    protected string $moduleNameLower = 'project';

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
        $this->app->singleton(ProjectInterface::class, ProjectRepository::class);
        $this->app->singleton(ProjectDiscussionsInterface::class, ProjectDiscussionsRepository::class);
        $this->app->singleton(ProjectMilestoneInterface::class, ProjectMilestoneRepository::class);
        $this->app->singleton(ProjectTicketsInterface::class, ProjectTicketsRepository::class);
        $this->app->singleton(ProjectFilesInterface::class, ProjectFilesRepository::class);
        $this->app->singleton(ProjectActivityInterface::class, ProjectActivityRepository::class);
        $this->app->singleton(ProjectNotesInterface::class, ProjectNotesRepository::class);
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
