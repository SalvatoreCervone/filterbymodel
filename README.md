# FilterByModel - Package Composer per Laravel

Sistema di **sicurezza perimetrale** e **filtraggio dati a livello di riga** (row-level security) per applicazioni Laravel. Permette di definire regole di visibilità basate sui modelli Eloquent e sulle competenze assegnate agli utenti.

## Funzionalità

- **Filtraggio automatico in lettura**: Global Scope Eloquent che limita i risultati delle query in base ai permessi dell'utente.
- **Validazione in scrittura/cancellazione**: Intercettazione automatica degli eventi `saving` e `deleting` per verificare il perimetro di sicurezza.
- **Relazioni Dirette (1:N) e Pivot (N:M)**: Supporto per entrambi i tipi di collegamento tra modelli.
- **Gerarchia ad albero**: Opzione `include_children` per includere automaticamente i figli nella catena gerarchica.
- **Gruppi logici AND/OR**: I filtri nello stesso gruppo operano in AND, gruppi diversi in OR.
- **Condizioni aggiuntive JSON**: Filtri extra configurabili in formato JSON.
- **Maschere Vue 3**: Componenti frontend pronti per la gestione admin e utente.

## Installazione

### 1. Aggiungi il package

```bash
composer require salvatore/filterbymodel
```

Il ServiceProvider viene registrato automaticamente tramite Laravel Package Discovery.

### 2. Pubblica le risorse

```bash
# Pubblica tutto (config, migrazioni, componenti Vue, servizi JS)
php artisan vendor:publish --tag=filterbymodel

# Oppure pubblica singolarmente:
php artisan vendor:publish --tag=filterbymodel-config
php artisan vendor:publish --tag=filterbymodel-migrations
php artisan vendor:publish --tag=filterbymodel-vue
php artisan vendor:publish --tag=filterbymodel-services
php artisan vendor:publish --tag=filterbymodel-routes
```

### 3. Esegui le migrazioni

```bash
php artisan migrate
```

## Configurazione

Modifica il file `config/filterbymodel.php` pubblicato:

```php
return [
    // Modelli disponibili nella maschera di configurazione
    'models' => [
        ['class' => 'App\Models\Anagrafica', 'name' => 'Anagrafica'],
        ['class' => 'App\Models\Ufficio',     'name' => 'Ufficio'],
        ['class' => 'App\Models\Qualifica',   'name' => 'Qualifica'],
    ],

    // Classe del modello User
    'user_model' => 'App\Models\User',

    // Colonna per la gerarchia ad albero
    'parent_column' => 'padre_id',

    // Configurazione rotte API
    'routes' => [
        'enabled'    => true,
        'prefix'     => 'api',
        'middleware' => ['api'],
    ],
];
```

## Utilizzo

### Proteggere un Modello Eloquent

Aggiungi il trait al modello che vuoi proteggere:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Salvatore\FilterByModel\Traits\IntercettaFiltriSistemi;
// oppure: use Salvatore\FilterByModel\Traits\HasModelFilters;

class Anagrafica extends Model
{
    use IntercettaFiltriSistemi;
    // oppure: use HasModelFilters;
}
```

Una volta applicato il trait, tutte le query su quel modello saranno automaticamente filtrate in base ai permessi dell'utente autenticato.

### Usare il Servizio Direttamente

```php
use Salvatore\FilterByModel\Services\ModelFilterService;

$service = app(ModelFilterService::class);

// Ottieni i filtri risolti per un modello specifico
$filters = $service->ottieniFiltriRisolti(Anagrafica::class);

// Per un utente specifico (non l'autenticato)
$filters = $service->ottieniFiltriRisolti(Anagrafica::class, $userId);
```

### Retrocompatibilità

Per i progetti che già utilizzano `AnagraficaFilterService`:

```php
use Salvatore\FilterByModel\Services\AnagraficaFilterService;

// Funziona esattamente come ModelFilterService
$service = app(AnagraficaFilterService::class);
```

## API Endpoints

| Metodo   | Endpoint                        | Descrizione                          |
|----------|---------------------------------|--------------------------------------|
| `GET`    | `/api/filter-definitions`       | Lista regole di visibilità           |
| `POST`   | `/api/filter-definitions`       | Crea/aggiorna regola                 |
| `DELETE` | `/api/filter-definitions/{id}`  | Rimuovi regola                       |
| `GET`    | `/api/available-models`         | Modelli configurati disponibili      |
| `GET`    | `/api/user-filters?user_id={id}`| Filtri attivi per utente             |
| `POST`   | `/api/user-filters`             | Assegna filtro a utente              |
| `DELETE` | `/api/user-filters/{id}`        | Rimuovi filtro utente                |

## Componenti Vue

Dopo la pubblicazione (`--tag=filterbymodel-vue`), i componenti saranno disponibili in `resources/js/Components/FilterByModel/`:

- **`FilterDefinitionManager.vue`** — Maschera admin per configurare le regole di visibilità.
- **`User/FilterManager.vue`** — Maschera per gestire i filtri assegnati a un utente.
- **`User/FilterForm.vue`** — Form per aggiungere nuovi filtri utente.
- **`User/FilterList.vue`** — Tabella con i filtri attivi e azioni di rimozione.

### Integrazione con il tuo componente di ricerca utente

Il `FilterManager.vue` espone uno **slot** `user-search` per integrare il tuo componente di ricerca:

```vue
<FilterManager>
  <template #user-search="{ onUserSelected }">
    <RicercaUser @personaleselezionato="onUserSelected" />
  </template>
</FilterManager>
```

## Licenza

MIT
# filterbymodel
