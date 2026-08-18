<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Nomi delle Tabelle Database
    |--------------------------------------------------------------------------
    |
    | I nomi delle tabelle utilizzate dal package per memorizzare le definizioni
    | dei filtri e i filtri assegnati agli utenti. Completamente personalizzabili.
    |
    */

    'tables' => [
        'filter_definitions' => 'filter_definitions',
        'user_filters'       => 'user_filters',
    ],

    /*
    |--------------------------------------------------------------------------
    | Configurazione Utente
    |--------------------------------------------------------------------------
    |
    | Impostazioni per il collegamento all'entità utente dell'applicazione:
    | - model: la classe del modello User (se null, usa auth.providers.users.model)
    | - foreign_key: il nome della colonna usata come foreign key verso l'utente
    |
    */

    'user' => [
        'model'       => env('FILTERBYMODEL_USER_MODEL', 'App\Models\User'),
        'foreign_key' => 'user_id',
    ],

    /*
    |--------------------------------------------------------------------------
    | Scoperta Automatica dei Modelli (Auto-Discovery)
    |--------------------------------------------------------------------------
    |
    | Di default, il package scansiona automaticamente tutti i modelli Eloquent
    | presenti in app/Models (e app/) senza alcuna configurazione manuale.
    |
    | - auto_discover: true per scansionare automaticamente i file su disco
    | - paths: percorsi/cartelle da scansionare (es. app_path('Models'), moduli DDD, ecc.)
    | - ignore: classi di modelli da escludere dalla lista
    | - explicit: modelli aggiuntivi o manuali (es. da altri package/vendor)
    |
    */

    'models' => [
        'auto_discover' => true,

        // Cartelle da scansionare per i modelli Eloquent (default: app/Models)
        'paths' => [
            app_path('Models'),
            // app_path('Domain/Accounting/Models'), // Esempio percorso personalizzato
        ],

        // Modelli da ignorare
        'ignore' => [
            \SalvatoreCervone\FilterByModel\Models\FilterDefinition::class,
            \SalvatoreCervone\FilterByModel\Models\UserFilter::class,
        ],

        // Modelli manuali/espliciti aggiuntivi
        'explicit' => [
            // ['class' => 'App\Models\Anagrafica', 'name' => 'Anagrafica'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Risoluzione Gerarchica ad Albero (Strutture Parent-Child)
    |--------------------------------------------------------------------------
    |
    | Come risolvere i nodi figli quando è attivo 'include_children'.
    | Il package supporta 4 modalità di rilevamento automatico:
    |
    | 1. Metodo nel modello: public function getParentColumnName(): string { return 'parent_id'; }
    | 2. Proprietà nel modello: public $parentColumn = 'parent_id';
    | 3. Mapping per modello in 'model_columns' (vedi sotto)
    | 4. Auto-detection automatica su Database Schema tramite la lista 'fallback_columns'
    |
    */

    'hierarchy' => [
        // Colonna predefinita globale di fallback
        'parent_column' => 'padre_id',

        // Mappatura specifica per singoli modelli (opzionale)
        'model_columns' => [
            // 'App\Models\Category' => 'parent_id',
            // 'App\Models\Office'   => 'parent_office_id',
        ],

        // Colonne verificate in ordine durante l'auto-detection su DB Schema
        'fallback_columns' => [
            'padre_id',
            'parent_id',
            'id_padre',
            'parent_code',
            'id_genitore',
            'parent_node_id',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Sicurezza e Autorizzazione
    |--------------------------------------------------------------------------
    |
    | - global_scope_name: nome univoco del Global Scope registrato sui modelli
    | - auth_id_resolver: closure personalizzata per risolvere l'ID dell'utente
    |   (se null, usa Auth::id())
    | - unauthorized_message: messaggio di errore sollevato in scrittura/cancellazione
    |
    */

    'security' => [
        'global_scope_name'    => 'filter_by_model_security_perimeter',
        'auth_id_resolver'     => null, // fn() => Auth::id(),
        'unauthorized_message' => 'Operazione bloccata. Non possiedi i requisiti di competenza necessari per interagire con questa risorsa.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Rotte del Package (API & Web Dashboard)
    |--------------------------------------------------------------------------
    |
    | Configurazione dinamica delle rotte fornite dal package:
    | - api: rotte REST per le operazioni CRUD (usate da UI e client JS)
    | - web: rotta per accedere alla Dashboard Amministrativa integrata
    |
    */

    'routes' => [
        // Rotte API REST
        'api' => [
            'enabled'    => true,
            'prefix'     => 'api',
            'middleware' => ['api'],
        ],

        // Dashboard Amministrativa Web (accessibile es. su /filterbymodel o /admin/filters)
        'web' => [
            'enabled'    => true,
            'prefix'     => 'filterbymodel',
            'middleware' => ['web'], // In produzione puoi aggiungere 'auth' o middleware di ruolo: ['web', 'auth']
        ],
    ],

];
