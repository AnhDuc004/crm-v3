<?php

namespace Modules\Customer\App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Modules\Customer\Repositories\Announcement\AnnouncementInterface;
use Modules\Customer\Repositories\Announcement\AnnouncementRepository;
use Modules\Customer\Repositories\Comment\CommentInterface;
use Modules\Customer\Repositories\Comment\CommentRepository;
use Modules\Customer\Repositories\Countries\CountryInterface;
use Modules\Customer\Repositories\Countries\CountryRepository;
use Modules\Customer\Repositories\Customer\CustomerInterface;
use Modules\Customer\Repositories\Customer\CustomerRepository;
use Modules\Customer\Repositories\Customer\CustomerGroupInterface;
use Modules\Customer\Repositories\Customer\CustomerGroupRepository;
use Modules\Customer\Repositories\Customer\CustomerAdminInterface;
use Modules\Customer\Repositories\Customer\CustomerAdminRepository;
use Modules\Customer\Repositories\Currency\CurrencyInterface;
use Modules\Customer\Repositories\Currency\CurrencyRepository;
use Modules\Customer\Repositories\File\FileInterface;
use Modules\Customer\Repositories\File\FileRepository;
use Modules\Customer\Repositories\Notes\NoteInterface;
use Modules\Customer\Repositories\Notes\NoteRepository;
use Modules\Customer\Repositories\Staff\StaffInterface;
use Modules\Customer\Repositories\Staff\StaffRepository;
use Modules\Customer\Repositories\Tags\TagInterface;
use Modules\Customer\Repositories\Tags\TagRepository;
use Modules\Customer\Repositories\Contact\ContactInterface;
use Modules\Customer\Repositories\Contact\ContactRepository;
use Modules\Customer\Repositories\CustomField\CustomFieldInterface;
use Modules\Customer\Repositories\CustomField\CustomFieldRepository;
use Modules\Customer\Repositories\Department\DepartmentInterface;
use Modules\Customer\Repositories\Department\DepartmentRepository;
use Modules\Customer\Repositories\Module\ModuleInterface;
use Modules\Customer\Repositories\Module\ModuleRepository;
use Modules\Customer\Repositories\Service\ServiceInterface;
use Modules\Customer\Repositories\Service\ServiceRepository;
use Modules\Customer\Repositories\SpamFilter\SpamFilterInterface;
use Modules\Customer\Repositories\SpamFilter\SpamFilterRepository;
use Modules\Customer\Repositories\Vault\VaultInterface;
use Modules\Customer\Repositories\Vault\VaultRepository;
use Modules\Customer\Repositories\Notification\NotificationInterface;
use Modules\Customer\Repositories\Notification\NotificationRepository;
use Modules\Customer\Repositories\Option\OptionInterface;
use Modules\Customer\Repositories\Option\OptionRepository;

class CustomerServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'Customer';

    protected string $moduleNameLower = 'customer';

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
        $this->app->singleton(CustomerInterface::class, CustomerRepository::class);
        $this->app->singleton(CustomerGroupInterface::class, CustomerGroupRepository::class);
        $this->app->singleton(CustomerAdminInterface::class, CustomerAdminRepository::class);
        $this->app->singleton(NoteInterface::class, NoteRepository::class);
        $this->app->singleton(TagInterface::class, TagRepository::class);
        // $this->app->singleton(CountryInterface::class, CountryRepository::class);
        // $this->app->singleton(CurrencyInterface::class, CurrencyRepository::class);
        $this->app->singleton(ContactInterface::class, ContactRepository::class);
        $this->app->singleton(CommentInterface::class, CommentRepository::class);
        // $this->app->singleton(StaffInterface::class, StaffRepository::class);
        $this->app->singleton(FileInterface::class, FileRepository::class);
        $this->app->singleton(VaultInterface::class, VaultRepository::class);
        // $this->app->singleton(TicketInterface::class, TicketRepository::class);
        $this->app->singleton(AnnouncementInterface::class, AnnouncementRepository::class);
        
        // $this->app->singleton(TicketsStatusInterface::class, TicketsStatusRepository::class);
        // $this->app->singleton(TicketsPriorityInterface::class, TicketsPriorityRepository::class);
        $this->app->singleton(SpamFilterInterface::class, SpamFilterRepository::class);
        $this->app->singleton(ServiceInterface::class, ServiceRepository::class);
        // $this->app->singleton(DepartmentInterface::class, DepartmentRepository::class);
        $this->app->singleton(ModuleInterface::class, ModuleRepository::class);
        $this->app->singleton(CustomFieldInterface::class, CustomFieldRepository::class);
        $this->app->singleton(NotificationInterface::class, NotificationRepository::class);
        $this->app->singleton(OptionInterface::class, OptionRepository::class);
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
