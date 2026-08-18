<?php

namespace SalvatoreCervone\FilterByModel;

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
     * Avvia i servizi del package (migrazioni, rotte, pubblicazioni).
     */
    public function boot(): void
    {
        $this->loadMigrations();
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
     * Carica le rotte API se abilitate nella configurazione.
     */
    protected function loadRoutes(): void
    {
        $routeConfig = config('filterbymodel.routes', []);

        if (!($routeConfig['enabled'] ?? true)) {
            return;
        }

        $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
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
            __DIR__ . '/../routes/api.php' => base_path('routes/filterbymodel.php'),
        ], 'filterbymodel-routes');

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
            __DIR__ . '/../resources/js/components/'     => resource_path('js/Components/FilterByModel'),
            __DIR__ . '/../resources/js/services/'       => resource_path('js/services'),
        ], 'filterbymodel');
    }
}
