# FilterByModel - Package Composer per Laravel

Package Laravel per la **sicurezza perimetrale** e il **filtraggio dati a livello di riga** (row-level security). Permette di definire regole di visibilità basate sui modelli Eloquent e sulle competenze assegnate agli utenti.

## Funzionalità

- **Protezione Automatica Zero-Code**: Protegge automaticamente tutti i modelli con regole definite senza richiedere di modificare il codice sorgente o inserire Trait.
- **Scoping Granulare per Modello Target (Novità)**: Possibilità di applicare una competenza utente a livello globale (su tutti i modelli che usano quel criterio) oppure circoscriverla selettivamente solo a specifici modelli target (es. solo per `Anagrafica` o `Contratto`).
- **Filtraggio automatico in lettura**: Global Scope Eloquent che limita i risultati delle query in base ai permessi dell'utente autenticato.
- **Validazione in scrittura/cancellazione**: Intercettazione automatica degli eventi `saving` e `deleting` per verificare il perimetro di sicurezza.
- **Relazioni Dirette (1:N) e Pivot (N:M)**: Supporto completo per entrambi i tipi di collegamento tra modelli, chiavi esterne personalizzate (`target_foreign_key`) e tabelle ponte.
- **Gerarchia ad albero**: Opzione `include_children` per includere automaticamente i discendenti nella catena gerarchica (`padre_id`, `parent_id`, ecc.).
- **Gruppi logici AND/OR**: I filtri nello stesso gruppo operano in AND, gruppi diversi in OR.
- **Condizioni aggiuntive JSON**: Filtri extra configurabili in formato JSON (`additional_where`) con supporto per valori `null`, stringhe e numeri.
- **Maschere Vue 3 con Live SQL Preview e Resoconto Globale**: Componenti frontend moderni per la gestione admin e utente con simulazione live della query SQL, autocomplete DB e clonazione rapida dei permessi.

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

Pubblica contemporaneamente la configurazione, le migrazioni, le viste Blade del pannello, i componenti Vue 3 e i servizi JavaScript:

```bash
php artisan vendor:publish --tag=filterbymodel
```

### Pubblicazione Selettiva

| Comando                                                     | Tag                        | Destinazione                             | Descrizione                                          |
| ----------------------------------------------------------- | -------------------------- | ---------------------------------------- | ---------------------------------------------------- |
| `php artisan vendor:publish --tag=filterbymodel-views`      | `filterbymodel-views`      | `resources/views/vendor/filterbymodel/`  | Vista Blade della Dashboard Amministrativa           |
| `php artisan vendor:publish --tag=filterbymodel-vue`        | `filterbymodel-vue`        | `resources/js/Components/FilterByModel/` | Componenti Vue 3 per la UI (Inertia/Vite)           |
| `php artisan vendor:publish --tag=filterbymodel-services`   | `filterbymodel-services`   | `resources/js/services/`                 | Client API JS (`filterService.js`)                   |
| `php artisan vendor:publish --tag=filterbymodel-config`     | `filterbymodel-config`     | `config/filterbymodel.php`               | File di configurazione                               |
| `php artisan vendor:publish --tag=filterbymodel-migrations` | `filterbymodel-migrations` | `database/migrations/`                   | Migrazioni per le tabelle del package                |
| `php artisan vendor:publish --tag=filterbymodel-routes`     | `filterbymodel-routes`     | `routes/filterbymodel-*.php`             | Rotte API e Web personalizzabili                     |

### Aggiornamento e Sovrascrittura (`--force`)

Se aggiorni il package e desideri sovrascrivere i file precedentemente pubblicati:

```bash
# Sovrascrive viste, componenti Vue e servizi JS
php artisan vendor:publish --tag=filterbymodel-views --force
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

Il package funziona **out-of-the-box con zero configurazione**: di default rileva e scansiona automaticamente tutti i modelli Eloquent presenti nella cartella standard di Laravel `app/Models` (e `app/`).

Se desideri personalizzare le impostazioni, modifica il file `config/filterbymodel.php` pubblicato:

```php
return [
    /*
    |--------------------------------------------------------------------------
    | Scoperta dei Modelli Eloquent
    |--------------------------------------------------------------------------
    | Di default (auto_discover = true), il package scansiona automaticamente
    | tutti i modelli Eloquent in app/Models.
    | Puoi aggiungere ulteriori percorsi custom o modelli espliciti se necessario.
    */
    'models' => [
        'auto_discover' => true,

        // Percorsi/cartelle da scansionare per i modelli Eloquent (default: app/Models)
        'paths' => [
            app_path('Models'),
            // app_path('Domain/Accounting/Models'), // Esempio percorso personalizzato
        ],

        // Modelli da ignorare
        'ignore' => [
            \SalvatoreCervone\FilterByModel\Models\FilterDefinition::class,
            \SalvatoreCervone\FilterByModel\Models\UserFilter::class,
        ],

        // Modelli manuali/espliciti aggiuntivi (opzionale)
        'explicit' => [
            // ['class' => 'App\Models\Anagrafica', 'name' => 'Anagrafica'],
        ],
    ],

    // Configurazione collegamento utente
    'user' => [
        'model'       => env('FILTERBYMODEL_USER_MODEL', 'App\Models\User'),
        'foreign_key' => 'user_id',
    ],

    // Risoluzione gerarchica ad albero
    'hierarchy' => [
        'parent_column' => 'padre_id',
    ],

    // Configurazione rotte del package (API REST e Dashboard Web)
    'routes' => [
        'api' => [
            'enabled'    => true,
            'prefix'     => 'api',
            'middleware' => ['api'],
        ],
        'web' => [
            'enabled'    => true,
            'prefix'     => 'filterbymodel', // es. http://tuo-dominio.test/filterbymodel
            'middleware' => ['web'],         // In prod: ['web', 'auth']
        ],
    ],
];
```

---

## Dashboard Web Amministrativa (Plug & Play)

Il package include un'**interfaccia web completa e reattiva** pronta all'uso. Senza configurare pagine o componenti, ti basta accedere dal browser all'URL:

```text
http://tuo-dominio.test/filterbymodel
```

La dashboard include 3 sezioni integrate:
1. **Regole Modelli**: Configurazione visuale delle relazioni con simulazione live delle query SQL generate.
2. **Competenze Utenti**: Autocomplete per selezionare gli operatori, assegnare perimetri e gestire le gerarchie ad albero.
3. **Resoconto Globale**: Statistiche aggregate, panoramica dello stato di tutti gli utenti e clonazione massiva rapida dei permessi.

> [!TIP]
> Puoi proteggere la dashboard impostando il middleware nel file `config/filterbymodel.php` (es. `['web', 'auth']` o un middleware personalizzato per soli amministratori).

---

## Utilizzo Backend

### 1. Protezione Automatica Zero-Code (Predefinita ⭐)

Di default, il package ha attiva la modalità **Auto-Apply** (`'auto_apply_to_all_models' => true`).

Questo significa che **non devi modificare i tuoi modelli Eloquent**:
- Non serve aggiungere alcun Trait.
- Non appena configuri una regola nella Dashboard per un modello (es. `App\Models\Anagraficaassenza`), Laravel applicherà **automaticamente** il Global Scope di lettura e i controlli in scrittura (`saving`) e cancellazione (`deleting`).
- Funziona anche su modelli provenienti da **package esterni o directory vendor** su cui non puoi modificare il codice sorgente.

---

### 2. Protezione Manuale tramite Trait (Opzionale)

Se preferisci specificare esplicitamente quali modelli proteggere a livello di codice, puoi disabilitare l'auto-applicazione in `.env`:
```env
FILTERBYMODEL_AUTO_APPLY=false
```

E aggiungere manualmente il Trait `HasModelFilters` (o l'equivalente italiano `IntercettaFiltriSistemi`) nei soli modelli desiderati:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use SalvatoreCervone\FilterByModel\Traits\HasModelFilters;

class Anagrafica extends Model
{
    use HasModelFilters;
}
```

---

### 3. Usare il Servizio Direttamente nel Codice

```php
use SalvatoreCervone\FilterByModel\Services\ModelFilterService;

$service = app(ModelFilterService::class);

// Ottieni i filtri risolti per un modello specifico (utente autenticato)
$filters = $service->ottieniFiltriRisolti(Anagrafica::class);

// Per un utente specifico (es. processi in background o simulazioni)
$filters = $service->ottieniFiltriRisolti(Anagrafica::class, $userId);
```

## API Endpoints

Le seguenti rotte REST sono esposte automaticamente se `routes.api.enabled` è `true`:

| Metodo   | Endpoint                         | Descrizione                                                |
| -------- | -------------------------------- | ---------------------------------------------------------- |
| `GET`    | `/api/filter-definitions`        | Lista di tutte le regole di visibilità                     |
| `POST`   | `/api/filter-definitions`        | Crea o aggiorna una regola                                 |
| `DELETE` | `/api/filter-definitions/{id}`   | Elimina una regola                                         |
| `GET`    | `/api/available-models`          | Modelli configurati disponibili                            |
| `GET`    | `/api/search-users`              | Ricerca operatori/utenti per autocomplete                  |
| `GET`    | `/api/user-filters-summary`      | Resoconto globale di tutti gli utenti e permessi bindati   |
| `GET`    | `/api/user-filters?user_id={id}` | Filtri attivi per un determinato utente                    |
| `POST`   | `/api/user-filters`              | Assegna un filtro a un utente                              |
| `POST`   | `/api/user-filters/copy`         | Clona i filtri da un utente sorgente a 1 o più destinatari |
| `DELETE` | `/api/user-filters/{id}`         | Rimuove un filtro utente                                   |

---

## Integrazione Frontend & Componenti Vue 3

Dopo aver eseguito `php artisan vendor:publish --tag=filterbymodel-vue`, i componenti Vue 3 saranno pronti all'uso nella directory `resources/js/Components/FilterByModel/`:

- **`FilterByModelDashboard.vue`** — **Dashboard Completa Unificata**: include navigazione a schede tra Regole Modelli e Competenze Operatori, ideale per un'integrazione a singola riga.
- **`FilterDefinitionManager.vue`** — Pannello di amministrazione per configurare le regole di visibilità con **simulazione query SQL live** in tempo reale (SELECT, UPDATE, DELETE).
- **`User/FilterManager.vue`** — Maschera principale con switch a schede (_Configura Operatore_ e _Resoconto Globale_).
- **`User/UserSummaryTable.vue`** — **Datatable di Resoconto Globale**: panoramica di tutti gli utenti, filtri per stato (con permessi / senza vincoli), badge di conteggio regole e azioni rapide di configurazione e clonazione.
- **`User/UserAutocomplete.vue`** — Autocomplete di ricerca operatore collegato al DB con debounce, navigazione da tastiera e supporto tabelle/campi custom.
- **`User/CopyFiltersModal.vue`** — Modale per **clonare e duplicare i permessi** su 1 o più operatori contemporaneamente (con modalità _Replace_ o _Merge_).
- **`User/FilterForm.vue`** — Modulo di assegnazione filtro con supporto inclusioni alberi gerarchici (`include_children`) e **scoping granulare per modello target** (permette di selezionare "Tutti i modelli" o scegliere specifici modelli a cui applicare la competenza).
- **`User/FilterList.vue`** — Tabella riassuntiva dei filtri attivi dell'utente con visualizzazione del target di applicazione (badge con modello specifico o ambito globale) e opzione di revoca.

### Resoconto Globale e Panoramica Utenti

Il componente `UserSummaryTable.vue` (o la scheda _Resoconto Globale_ in `FilterManager.vue`) offre agli amministratori:

1. **Indicatori Statistici (Stat Cards)**: totale operatori, quanti hanno filtri di sicurezza attivi e quanti sono senza vincoli.
2. **Filtri di Stato Rapidi**: pulsanti per filtrare all'istante _Tutti_, _Con Permessi_ o _Senza Vincoli_.
3. **Ricerca Testuale**: filtro immediato per nome, cognome, email o ID.
4. **Azioni Rapide Dirette**: pulsante _"Configura"_ per passare all'editor del singolo operatore e pulsante _"Clona"_ per duplicarne le competenze.

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
  @filters-cloned="(result) => console.log('Clonati:', result)"
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

Puoi integrare direttamente l'intera dashboard con una sola riga:

```vue
<script setup>
import FilterByModelDashboard from "@/Components/FilterByModel/FilterByModelDashboard.vue";
</script>

<template>
  <FilterByModelDashboard />
</template>
```

Oppure comporre manualmente i singoli componenti con i tuoi tab personalizzati:

```vue
<script setup>
import FilterDefinitionManager from "@/Components/FilterByModel/FilterDefinitionManager.vue";
import FilterManager from "@/Components/FilterByModel/User/FilterManager.vue";
import { ref } from "vue";

const activeTab = ref("definitions");
</script>

<template>
  <div class="p-6 max-w-7xl mx-auto">
    <!-- Tab di navigazione -->
    <div class="flex gap-4 mb-6">
      <button
        @click="activeTab = 'definitions'"
        :class="
          activeTab === 'definitions'
            ? 'bg-indigo-600 text-white'
            : 'bg-slate-200 text-slate-700'
        "
        class="px-4 py-2 rounded-xl font-semibold text-sm transition-all shadow-xs"
      >
        Regole di Visibilità (Admin)
      </button>
      <button
        @click="activeTab = 'users'"
        :class="
          activeTab === 'users'
            ? 'bg-indigo-600 text-white'
            : 'bg-slate-200 text-slate-700'
        "
        class="px-4 py-2 rounded-xl font-semibold text-sm transition-all shadow-xs"
      >
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
import { createApp } from "vue";
import FilterDefinitionManager from "./Components/FilterByModel/FilterDefinitionManager.vue";
import FilterManager from "./Components/FilterByModel/User/FilterManager.vue";

const app = createApp({});
app.component("filter-definition-manager", FilterDefinitionManager);
app.component("filter-manager", FilterManager);
app.mount("#app");
```

Nella tua vista Blade (es. `resources/views/admin/filters.blade.php`):

```html
@extends('layouts.app') @section('content')
<div id="app" class="container mx-auto py-8">
  <filter-definition-manager></filter-definition-manager>
</div>
@endsection
```

### Strutture ad Albero e Colonne Gerarchiche (`include_children`)

Quando si abilitano i nodi figli (`include_children`), il package calcola ricorsivamente tutti i discendenti:

- **Dalla UI Admin (`FilterDefinitionManager.vue`)**: puoi indicare esplicitamente il campo gerarchico (es. `parent_id`, `id_padre`, `padre_id`).
- **Se lasciato vuoto**: usa `padre_id` (o la configurazione globale) oppure rileva automaticamente le colonne convenzionali su DB (`padre_id`, `parent_id`, `id_padre`, `parent_code`, `id_genitore`).

### Scoping Granulare per Modello Target

Di default, quando assegni a un utente una competenza su un'entità (es. *Azienda: ID 5*), tale filtro viene applicato su **tutti i modelli Eloquent** protetti che fanno riferimento a quella regola.

Se invece desideri che un operatore veda una determinata sede o filiale solo su un modello specifico (ad esempio solo sulle `Fatture` ma non sulle `Presenze`), puoi selezionare i **Modelli Target**:
- **Tutti i modelli** (default, `target_model = null`): la competenza si applica a tutte le entità collegate a quel criterio.
- **Modelli specifici** (`target_model = App\Models\Fattura`): la competenza si applicherà unicamente a quel modello, lasciando inalterati gli altri.

### Condizioni Aggiuntive JSON (`additional_where`)

Nelle regole di visibilità puoi definire vincoli extra memorizzati come oggetto chiave-valore JSON:
```json
{
  "deleted_at": null,
  "is_active": true,
  "tipo_record": "UFFICIALE"
}
```
Il generatore SQL e il verificatore di sicurezza a runtime applicano automaticamente queste condizioni sia nelle query Eloquent (`whereNull` per valori nulli, `where` standard per scalari) sia nella validazione durante il salvataggio o la cancellazione dei record.

## Licenza

Questo package è software open source rilasciato sotto i termini della licenza [MIT](LICENSE).
