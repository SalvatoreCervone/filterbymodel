# FilterByModel - Package Composer per Laravel

Package Laravel per la **sicurezza perimetrale** e il **filtraggio dati a livello di riga** (row-level security). Permette di definire regole di visibilità basate sui modelli Eloquent e sulle competenze assegnate agli utenti.

## Funzionalità

- **Filtraggio automatico in lettura**: Global Scope Eloquent che limita i risultati delle query in base ai permessi dell'utente autenticato.
- **Validazione in scrittura/cancellazione**: Intercettazione automatica degli eventi `saving` e `deleting` per verificare il perimetro di sicurezza.
- **Relazioni Dirette (1:N) e Pivot (N:M)**: Supporto completo per entrambi i tipi di collegamento tra modelli.
- **Gerarchia ad albero**: Opzione `include_children` per includere automaticamente i figli nella catena gerarchica (`padre_id`).
- **Gruppi logici AND/OR**: I filtri nello stesso gruppo operano in AND, gruppi diversi in OR.
- **Condizioni aggiuntive JSON**: Filtri extra configurabili in formato JSON (`additional_where`).
- **Maschere Vue 3 con Query Preview**: Componenti frontend moderni per la gestione admin e utente con simulazione live della query SQL.

## Installazione

### 1. Aggiungi il package via Composer

```bash
composer require salvatorecervone/filterbymodel
```

Il ServiceProvider `SalvatoreCervone\FilterByModel\FilterByModelServiceProvider` viene registrato automaticamente tramite il Package Discovery di Laravel.

---

## Comandi di Pubblicazione Risorse

Puoi pubblicare tutti gli asset del package con un unico comando, oppure pubblicare selettivamente solo ciò di cui hai bisogno:

### Pubblicazione Completa (Raccomandata)

Pubblica contemporaneamente la configurazione, le migrazioni, i componenti Vue 3 e i servizi JavaScript:

```bash
php artisan vendor:publish --tag=filterbymodel
```

### Pubblicazione Selettiva

| Comando | Tag | Destinazione | Descrizione |
|---|---|---|---|
| `php artisan vendor:publish --tag=filterbymodel-vue` | `filterbymodel-vue` | `resources/js/Components/FilterByModel/` | Componenti Vue 3 per la UI |
| `php artisan vendor:publish --tag=filterbymodel-services` | `filterbymodel-services` | `resources/js/services/` | Client API JS (`filterService.js`) |
| `php artisan vendor:publish --tag=filterbymodel-config` | `filterbymodel-config` | `config/filterbymodel.php` | File di configurazione |
| `php artisan vendor:publish --tag=filterbymodel-migrations` | `filterbymodel-migrations` | `database/migrations/` | Migrazioni per le tabelle del package |
| `php artisan vendor:publish --tag=filterbymodel-routes` | `filterbymodel-routes` | `routes/filterbymodel.php` | Rotte API (per sovrascrittura manuale) |

### Aggiornamento e Sovrascrittura (`--force`)

Se aggiorni il package e desideri sovrascrivere i file precedentemente pubblicati (es. componenti Vue aggiornati):

```bash
# Sovrascrive solo i componenti Vue e i servizi JS
php artisan vendor:publish --tag=filterbymodel-vue --force
php artisan vendor:publish --tag=filterbymodel-services --force

# Oppure sovrascrive tutto
php artisan vendor:publish --tag=filterbymodel --force
```

---

### 3. Esegui le migrazioni

```bash
php artisan migrate
```

## Configurazione

Modifica il file `config/filterbymodel.php` pubblicato per registrare i tuoi modelli e impostare le preferenze:

```php
return [
    // Modelli disponibili nella maschera di configurazione admin
    'models' => [
        ['class' => 'App\Models\Anagrafica', 'name' => 'Anagrafica'],
        ['class' => 'App\Models\Ufficio',     'name' => 'Ufficio'],
        ['class' => 'App\Models\Qualifica',   'name' => 'Qualifica'],
    ],

    // Modello utente dell'applicazione
    'user_model' => 'App\Models\User',

    // Colonna predefinita per la gerarchia ad albero
    'parent_column' => 'padre_id',

    // Configurazione rotte API del package
    'routes' => [
        'enabled'    => true,
        'prefix'     => 'api',
        'middleware' => ['api'],
    ],
];
```

## Utilizzo Backend

### 1. Proteggere un Modello Eloquent

Aggiungi il trait al modello che desideri proteggere con la sicurezza perimetrale:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use SalvatoreCervone\FilterByModel\Traits\IntercettaFiltriSistemi;
// oppure alias: use SalvatoreCervone\FilterByModel\Traits\HasModelFilters;

class Anagrafica extends Model
{
    use IntercettaFiltriSistemi;
    // oppure: use HasModelFilters;
}
```

Una volta applicato il trait, tutte le query su quel modello saranno **automaticamente filtrate in lettura e verificate in scrittura/cancellazione** in base ai permessi dell'utente autenticato.

### 2. Usare il Servizio Direttamente nel Codice

```php
use SalvatoreCervone\FilterByModel\Services\ModelFilterService;

$service = app(ModelFilterService::class);

// Ottieni i filtri risolti per un modello specifico (utente autenticato)
$filters = $service->ottieniFiltriRisolti(Anagrafica::class);

// Per un utente specifico (es. processi in background o simulazioni)
$filters = $service->ottieniFiltriRisolti(Anagrafica::class, $userId);
```

## API Endpoints

Le seguenti rotte REST sono esposte automaticamente se `routes.enabled` è `true`:

| Metodo   | Endpoint                        | Descrizione                          |
|----------|---------------------------------|--------------------------------------|
| `GET`    | `/api/filter-definitions`       | Lista di tutte le regole di visibilità|
| `POST`   | `/api/filter-definitions`       | Crea o aggiorna una regola           |
| `DELETE` | `/api/filter-definitions/{id}`  | Elimina una regola                   |
| `GET`    | `/api/available-models`         | Modelli configurati disponibili      |
| `GET`    | `/api/search-users`             | Ricerca operatori/utenti per autocomplete |
| `GET`    | `/api/user-filters?user_id={id}`| Filtri attivi per un determinato utente |
| `POST`   | `/api/user-filters`             | Assegna un filtro a un utente        |
| `DELETE` | `/api/user-filters/{id}`        | Rimuove un filtro utente             |

---

## Integrazione Frontend & Componenti Vue 3

Dopo aver eseguito `php artisan vendor:publish --tag=filterbymodel-vue`, i componenti Vue 3 saranno pronti all'uso nella directory `resources/js/Components/FilterByModel/`:

- **`FilterDefinitionManager.vue`** — Pannello di amministrazione per configurare le regole di visibilità con **simulazione query SQL live** in tempo reale (SELECT, UPDATE, DELETE).
- **`User/FilterManager.vue`** — Maschera principale per assegnare e gestire i filtri dei singoli utenti (include di default l'autocomplete di ricerca).
- **`User/UserAutocomplete.vue`** — Componente Autocomplete di ricerca operatore collegato al DB, con debounce, navigazione da tastiera e supporto a tabelle/colonne personalizzate.
- **`User/FilterForm.vue`** — Modulo di assegnazione filtro con supporto inclusioni alberi gerarchici (`include_children`).
- **`User/FilterList.vue`** — Tabella riassuntiva dei filtri attivi dell'utente con opzione di revoca.

### Configurazione di Default e Personalizzazione Ricerca Utente

Il componente `FilterManager.vue` include **già di default l'autocomplete integrato** collegato alla tabella `users` e alla colonna `id`. Restituisce **obbligatoriamente il solo ID numerico/stringa**:

```vue
<!-- Utilizzo standard (collegato di default alla tabella 'users' e campo 'id') -->
<FilterManager />

<!-- Personalizzazione tabella, campo ID, campo etichetta e placeholder -->
<FilterManager 
  user-table="operatori"
  user-id-field="id_operatore"
  user-label-field="cognome_nome"
  placeholder="Cerca per matricola o cognome..."
  @user-selected="(id) => console.log('ID Operatore selezionato:', id)"
/>
```

#### Sovrascrittura con Slot Personalizzato (Opzionale)

Se preferisci usare un tuo componente di ricerca custom invece dell'autocomplete integrato, puoi usare lo slot `#user-search`:

```vue
<FilterManager>
  <!-- Il tuo componente deve restituire l'ID chiamando la funzione onUserSelected(id) -->
  <template #user-search="{ onUserSelected }">
    <MyCustomUserSearch @selected="(user) => onUserSelected(user.id)" />
  </template>
</FilterManager>
```

### Esempio d'uso con Inertia.js

Crea una pagina amministrativa (es. `resources/js/Pages/Admin/SecurityFilters.vue`):

```vue
<script setup>
import FilterDefinitionManager from '@/Components/FilterByModel/FilterDefinitionManager.vue';
import FilterManager from '@/Components/FilterByModel/User/FilterManager.vue';
import { ref } from 'vue';

const activeTab = ref('definitions');
</script>

<template>
  <div class="p-6 max-w-7xl mx-auto">
    <!-- Tab di navigazione -->
    <div class="flex gap-4 mb-6">
      <button 
        @click="activeTab = 'definitions'" 
        :class="activeTab === 'definitions' ? 'bg-indigo-600 text-white' : 'bg-slate-200 text-slate-700'"
        class="px-4 py-2 rounded-xl font-semibold text-sm transition-all shadow-xs">
        Regole di Visibilità (Admin)
      </button>
      <button 
        @click="activeTab = 'users'" 
        :class="activeTab === 'users' ? 'bg-indigo-600 text-white' : 'bg-slate-200 text-slate-700'"
        class="px-4 py-2 rounded-xl font-semibold text-sm transition-all shadow-xs">
        Competenze Utenti
      </button>
    </div>

    <!-- Gestione Regole -->
    <FilterDefinitionManager v-if="activeTab === 'definitions'" />

    <!-- Gestione Filtri Utente con Autocomplete integrato -->
    <FilterManager v-else-if="activeTab === 'users'" />
  </div>
</template>
```

### Esempio d'uso con Blade + Vue 3 / Vite

Se utilizzi Blade tradizionale con Vue montato su un elemento:

Nel tuo file `resources/js/app.js`:
```javascript
import { createApp } from 'vue';
import FilterDefinitionManager from './Components/FilterByModel/FilterDefinitionManager.vue';
import FilterManager from './Components/FilterByModel/User/FilterManager.vue';

const app = createApp({});
app.component('filter-definition-manager', FilterDefinitionManager);
app.component('filter-manager', FilterManager);
app.mount('#app');
```

Nella tua vista Blade (es. `resources/views/admin/filters.blade.php`):
```html
@extends('layouts.app')

@section('content')
<div id="app" class="container mx-auto py-8">
    <filter-definition-manager></filter-definition-manager>
</div>
@endsection
```

### Strutture ad Albero e Colonne Gerarchiche (`include_children`)

Quando si abilitano i nodi figli (`include_children`), il package calcola ricorsivamente tutti i discendenti:
- **Dalla UI Admin (`FilterDefinitionManager.vue`)**: puoi indicare esplicitamente il campo gerarchico (es. `parent_id`, `id_padre`, `padre_id`).
- **Se lasciato vuoto**: usa `padre_id` (o la configurazione globale) oppure rileva automaticamente le colonne convenzionali su DB (`padre_id`, `parent_id`, `id_padre`, `parent_code`, `id_genitore`).

## Licenza

Questo package è software open source rilasciato sotto i termini della licenza [MIT](LICENSE).
