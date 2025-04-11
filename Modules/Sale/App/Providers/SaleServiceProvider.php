<?php

namespace Modules\Sale\App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Modules\Sale\Repositories\CreditNotes\CreditNotesInterface;
use Modules\Sale\Repositories\CreditNotes\CreditNotesRepository;
use Modules\Sale\Repositories\Estimate\EstimateInterface;
use Modules\Sale\Repositories\Estimate\EstimateRepository;
use Modules\Sale\Repositories\Invoice\InvoiceInterface;
use Modules\Sale\Repositories\Invoice\InvoiceRepository;
use Modules\Sale\Repositories\Payment\PaymentInterface;
use Modules\Sale\Repositories\Payment\PaymentRepository;
use Modules\Sale\Repositories\PaymentModes\PaymentModesInterface;
use Modules\Sale\Repositories\PaymentModes\PaymentModesRepository;
use Modules\Sale\Repositories\Proposals\ProposalInterface;
use Modules\Sale\Repositories\Proposals\ProposalRepository;
use Modules\Sale\Repositories\Item\ItemInterface;
use Modules\Sale\Repositories\Item\ItemRepository;
use Modules\Sale\Repositories\Itemable\ItemableInterface;
use Modules\Sale\Repositories\Itemable\ItemableRepository;
use Modules\Sale\Repositories\ItemGroup\ItemGroupInterface;
use Modules\Sale\Repositories\ItemGroup\ItemGroupRepository;
use Modules\Sale\Repositories\Taxes\TaxesInterface;
use Modules\Sale\Repositories\Taxes\TaxesRepository;
use Modules\Sale\Repositories\ProposalComment\ProposalCommentInterface;
use Modules\Sale\Repositories\ProposalComment\ProposalCommentRepository;
use Modules\Sale\Repositories\SaleActivity\SaleActivityInterface;
use Modules\Sale\Repositories\SaleActivity\SaleActivityRepository;
use Modules\Sale\Repositories\CreditNotesRefunds\CreditNotesRefundsInterface;
use Modules\Sale\Repositories\CreditNotesRefunds\CreditNotesRefundsRepository;
use Modules\Sale\Repositories\Credits\CreditsInterface;
use Modules\Sale\Repositories\Credits\CreditsRepository;

class SaleServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'Sale';

    protected string $moduleNameLower = 'sale';

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
        $this->app->singleton(CreditNotesInterface::class, CreditNotesRepository::class); 
        $this->app->singleton(ItemGroupInterface::class, ItemGroupRepository::class);
        $this->app->singleton(ItemInterface::class, ItemRepository::class);
        $this->app->singleton(ProposalInterface::class, ProposalRepository::class);
        $this->app->singleton(EstimateInterface::class, EstimateRepository::class);
        $this->app->singleton(InvoiceInterface::class, InvoiceRepository::class);
        $this->app->singleton(PaymentInterface::class, PaymentRepository::class);
        $this->app->singleton(PaymentModesInterface::class, PaymentModesRepository::class);
        $this->app->singleton(ItemableInterface::class, ItemableRepository::class);
        $this->app->singleton(TaxesInterface::class, TaxesRepository::class);
        $this->app->singleton(ProposalCommentInterface::class, ProposalCommentRepository::class);
        $this->app->singleton(SaleActivityInterface::class, SaleActivityRepository::class);
        $this->app->singleton(CreditNotesRefundsInterface::class, CreditNotesRefundsRepository::class); 
        $this->app->singleton(CreditsInterface::class, CreditsRepository::class); 
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
