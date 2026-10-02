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
        'filter_definitions'    => 'filter_definitions',
        'user_filters'          => 'user_filters',
        'filter_user_bypasses'  => 'filter_user_bypasses',
    ],

    /*
    |--------------------------------------------------------------------------
    | Configurazione Utente / Operatore
    |--------------------------------------------------------------------------
    |
    | Impostazioni per il collegamento e la visualizzazione degli operatori:
    | - model: la classe del modello User (se null, usa auth.providers.users.model)
    | - foreign_key: il nome della colonna usata come foreign key verso l'utente
    | - display_fields: campi verificati in ordine per comporre il nome visualizzato (es. ['cognome', 'nome'])
    | - secondary_fields: campi secondari mostrati come sottotitolo (es. ['email', 'matricola', 'username'])
    | - searchable_fields: campi inclusi nella ricerca LIKE
    |
    */

    'user' => [
        'model' => env('FILTERBYMODEL_USER_MODEL', 'App\Models\User'),
        'table' => env('FILTERBYMODEL_USER_TABLE', 'users'),
        'foreign_key' => 'user_id',
        'primary_key' => 'id',
        'display_fields' => ['name'],
        'secondary_fields' => ['email'],
        'searchable_fields' => ['name', 'email'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Modelli Utente Interrogabili dalla Dashboard (Multi-Modello)
    |--------------------------------------------------------------------------
    |
    | Definisce i modelli utente disponibili nella Dashboard per la ricerca
    | e l'assegnazione di filtri o bypass globale.
    |
    | Per ogni modello si possono specificare:
    | - label:       Nome visivo nella UI (es. "Utenti", "Amministratori")
    | - table:       Nome della tabella database (se vuoto, viene risolto dal modello)
    | - foreign_key: Chiave esterna per collegare l'utente ai filtri
    | - primary_key: Chiave primaria della tabella utente
    | - display:     Array di campi concatenati per formare l'etichetta principale
    |                Es. ['cognome', 'nome'] => "Rossi Mario"
    |                Es. ['matricola', 'name'] => "MAT-001 Mario Rossi"
    | - separator:   Separatore tra i campi del display (default: ' ')
    | - subtext:     Campi mostrati come sottotesto (es. ['email', 'ruolo'])
    | - searchable:  Campi inclusi nella ricerca LIKE
    |
    */

    'users' => [
        // Modelli interrogabili dalla Dashboard per assegnare filtri o bypass
        'models' => [
            'App\Models\User' => [
                'label'       => 'Utenti',
                'table'       => 'users',
                'foreign_key' => 'user_id',
                'primary_key' => 'id',
                'display'     => ['name'],
                'separator'   => ' ',
                'subtext'     => ['email'],
                'searchable'  => ['name', 'cognome', 'nome', 'email', 'matricola'],
            ],
            // Esempio: modello Admin separato
            // 'App\Models\Admin' => [
            //     'label'       => 'Amministratori',
            //     'table'       => 'admins',
            //     'foreign_key' => 'admin_id',
            //     'primary_key' => 'id',
            //     'display'     => ['name', 'email'],
            //     'separator'   => ' — ',
            //     'subtext'     => ['email', 'ruolo'],
            //     'searchable'  => ['name', 'email', 'username'],
            // ],
        ],

        // Super-utenti con bypass hardcodato (bootstrap / CLI / fallback)
        // Questi ID scavalcano sempre i filtri senza necessità di configurazione a DB
        'super_user_ids' => [],
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
        'ignore' => [\SalvatoreCervone\FilterByModel\Models\FilterDefinition::class, \SalvatoreCervone\FilterByModel\Models\UserFilter::class, \SalvatoreCervone\FilterByModel\Models\UserBypass::class],

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
        // Colonna predefinita globale di fallback per il collegamento al genitore
        'parent_column' => 'padre_id',

        // Colonna identificativa/chiave predefinita globale per il nodo dell'albero (default 'id')
        'key_column' => 'id',

        // Mappatura specifica della colonna genitore per singoli modelli (opzionale)
        'model_columns' => [
            // 'App\Models\Category' => 'parent_id',
            // 'App\Models\Office'   => 'parent_office_id',
        ],

        // Mappatura specifica della colonna chiave per singoli modelli (opzionale)
        'model_key_columns' => [
            // 'App\Models\Category' => 'id',
            // 'App\Models\Office'   => 'codice',
        ],

        // Colonne genitore verificate in ordine durante l'auto-detection su DB Schema
        'fallback_columns' => ['padre_id', 'parent_id', 'id_padre', 'parent_code', 'id_genitore', 'parent_node_id'],

        // Colonne chiave/identificative verificate in ordine durante l'auto-detection su DB Schema
        'fallback_key_columns' => ['id', 'codice', 'code', 'uuid', 'matricola', 'pk'],
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
    | - unassigned_behavior: comportamento per utenti senza bypass e senza filtri
    |   su un modello protetto:
    |   - 'deny' (CONSIGLIATO): Fail-Closed, l'utente non vede nulla (WHERE 1 = 0)
    |   - 'allow': Fail-Open, l'utente vede tutto (comportamento permissivo)
    |
    */

    'security' => [
        // Se true, applica automaticamente il Global Scope e i controlli a tutti i modelli con regole definite senza richiedere il trait
        'auto_apply_to_all_models' => env('FILTERBYMODEL_AUTO_APPLY', true),

        'global_scope_name' => 'filter_by_model_security_perimeter',
        'auth_id_resolver' => null, // fn() => Auth::id(),
        'unauthorized_message' => 'Operazione bloccata. Non possiedi i requisiti di competenza necessari per interagire con questa risorsa.',

        // Comportamento per utenti NON bypassati e SENZA filtri assegnati per un modello protetto:
        // 'deny' = Fail-Closed (WHERE 1 = 0) -> L'utente non configurato non vede nulla -> CONSIGLIATO
        // 'allow' = Fail-Open -> L'utente non configurato vede tutto
        'unassigned_behavior' => env('FILTERBYMODEL_UNASSIGNED_BEHAVIOR', 'deny'),
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
            'enabled' => true,
            'prefix' => 'api',
            'middleware' => ['api'],
        ],

        // Dashboard Amministrativa Web (accessibile es. su /filterbymodel o /admin/filters)
        'web' => [
            'enabled' => true,
            'prefix' => 'filterbymodel',
            'middleware' => ['web'], // In produzione puoi aggiungere 'auth' o middleware di ruolo: ['web', 'auth']
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Introspezione e Suggerimento Valori (Visual Rule Builder)
    |--------------------------------------------------------------------------
    |
    | Configurazione per l'estrazione e i suggerimenti dei valori delle colonne:
    | - distinct_values_limit: soglia massima di valori univoci estratti per colonna (default: 50)
    | - search_limit: limite di risultati durante la ricerca live/autocomplete (default: 15)
    | - cache_ttl_seconds: durata della cache per i valori (in secondi, default: 300)
    |
    */
    'introspection' => [
        'distinct_values_limit' => env('FILTERBYMODEL_DISTINCT_LIMIT', 50),
        'search_limit'          => 15,
        'cache_ttl_seconds'     => 300,
    ],
];
