<?php

namespace SalvatoreCervone\FilterByModel;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use SalvatoreCervone\FilterByModel\Services\ModelFilterService;

class FilterByModelServiceProvider extends ServiceProvider
{
    /**
     * Registra i servizi del package nel container IoC.
     */
    public function register(): void
    {
        // Merge della configurazione di default del package
        $this->mergeConfigFrom(
            __DIR__ . '/../config/filterbymodel.php',
            'filterbymodel'
        );

        // Registra il servizio principale come Singleton
        $this->app->singleton(ModelFilterService::class, function ($app) {
            return new ModelFilterService();
        });
    }

    /**
     * Avvia i servizi del package (migrazioni, rotte, viste, pubblicazioni).
     */
    public function boot(): void
    {
        $this->loadMigrations();
        $this->loadViews();
        $this->loadRoutes();
        $this->registerPublishing();
    }

    /**
     * Carica le migrazioni del package.
     */
    protected function loadMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

    /**
     * Carica le viste Blade del package.
     */
    protected function loadViews(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'filterbymodel');
    }

    /**
     * Carica le rotte API e Web (Dashboard) se abilitate nella configurazione.
     */
    protected function loadRoutes(): void
    {
        $routeConfig = config('filterbymodel.routes', []);

        // 1. Caricamento Rotte API REST
        $apiConfig = isset($routeConfig['api']) ? $routeConfig['api'] : $routeConfig;
        if ($apiConfig['enabled'] ?? true) {
            $apiPrefix = $apiConfig['prefix'] ?? 'api';
            $apiMiddleware = $apiConfig['middleware'] ?? ['api'];

            Route::prefix($apiPrefix)
                ->middleware($apiMiddleware)
                ->group(__DIR__ . '/../routes/api.php');
        }

        // 2. Caricamento Rotte Web Dashboard Amministrativa
        $webConfig = $routeConfig['web'] ?? null;
        if ($webConfig && ($webConfig['enabled'] ?? true)) {
            $webPrefix = $webConfig['prefix'] ?? 'filterbymodel';
            $webMiddleware = $webConfig['middleware'] ?? ['web'];

            Route::prefix($webPrefix)
                ->middleware($webMiddleware)
                ->group(__DIR__ . '/../routes/web.php');
        }
    }

    /**
     * Registra le risorse pubblicabili tramite `vendor:publish`.
     */
    protected function registerPublishing(): void
    {
        if (!$this->app->runningInConsole()) {
            return;
        }

        // Pubblicazione della configurazione
        $this->publishes([
            __DIR__ . '/../config/filterbymodel.php' => config_path('filterbymodel.php'),
        ], 'filterbymodel-config');

        // Pubblicazione delle migrazioni
        $this->publishes([
            __DIR__ . '/../database/migrations/' => database_path('migrations'),
        ], 'filterbymodel-migrations');

        // Pubblicazione delle rotte (per personalizzazione)
        $this->publishes([
            __DIR__ . '/../routes/api.php' => base_path('routes/filterbymodel-api.php'),
            __DIR__ . '/../routes/web.php' => base_path('routes/filterbymodel-web.php'),
        ], 'filterbymodel-routes');

        // Pubblicazione delle viste Blade
        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/filterbymodel'),
        ], 'filterbymodel-views');

        // Pubblicazione dei componenti Vue e del servizio JS
        $this->publishes([
            __DIR__ . '/../resources/js/components/' => resource_path('js/Components/FilterByModel'),
        ], 'filterbymodel-vue');

        $this->publishes([
            __DIR__ . '/../resources/js/services/' => resource_path('js/services'),
        ], 'filterbymodel-services');

        // Tag unico per pubblicare tutto in una volta
        $this->publishes([
            __DIR__ . '/../config/filterbymodel.php'    => config_path('filterbymodel.php'),
            __DIR__ . '/../database/migrations/'         => database_path('migrations'),
            __DIR__ . '/../resources/views'              => resource_path('views/vendor/filterbymodel'),
            __DIR__ . '/../resources/js/components/'     => resource_path('js/Components/FilterByModel'),
            __DIR__ . '/../resources/js/services/'       => resource_path('js/services'),
        ], 'filterbymodel');
    }
}
