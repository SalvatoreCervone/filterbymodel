<?php

namespace SalvatoreCervone\FilterByModel\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use SalvatoreCervone\FilterByModel\Models\UserBypass;
use SalvatoreCervone\FilterByModel\Models\UserFilter;
use SalvatoreCervone\FilterByModel\Services\ModelFilterService;

class UserFilterController extends Controller
{
    /**
     * Elenco dei filtri attivi per un utente specifico.
     */
    public function index(Request $request): JsonResponse
    {
        $userFk = config('filterbymodel.user.foreign_key', 'user_id');

        $request->validate([
            $userFk => 'required',
        ]);

        $filters = UserFilter::where($userFk, $request->input($userFk))
            ->orderBy('group')
            ->orderBy('filterable_type')
            ->get();

        return response()->json($filters);
    }

    /**
     * Assegna un nuovo filtro a un utente.
     */
    public function store(Request $request): JsonResponse
    {
        $userFk = config('filterbymodel.user.foreign_key', 'user_id');

        $validated = $request->validate([
            $userFk            => 'required',
            'filterable_type'  => 'required|string|max:255',
            'filterable_id'    => 'required',
            'target_model'     => 'nullable|string|max:255',
            'include_children' => 'sometimes|boolean',
            'parent_column'    => 'nullable|string|max:255',
            'group'            => 'required|integer|min:1',
        ]);

        $targetModel = !empty($validated['target_model']) ? $validated['target_model'] : null;
        $validated['target_model'] = $targetModel;

        // Verifica duplicati: evita l'assegnazione duplicata dello stesso filtro/gruppo/target_model all'utente
        $query = UserFilter::where($userFk, $validated[$userFk])
            ->where('filterable_type', $validated['filterable_type'])
            ->where('filterable_id', $validated['filterable_id'])
            ->where('group', $validated['group']);

        if ($targetModel !== null) {
            $query->where('target_model', $targetModel);
        } else {
            $query->whereNull('target_model');
        }

        if ($query->exists()) {
            $targetLabel = $targetModel ? 'per la scheda ' . class_basename($targetModel) : 'a livello globale';
            return response()->json([
                'message' => "Questa competenza è già stata assegnata all'operatore {$targetLabel} per il Gruppo {$validated['group']}.",
            ], 422);
        }

        $filter = UserFilter::create($validated);

        return response()->json(['data' => $filter], 201);
    }

    /**
     * Rimuove un filtro utente.
     */
    public function destroy(int $id): JsonResponse
    {
        $filter = UserFilter::findOrFail($id);
        $filter->delete();

        return response()->json(['message' => 'Filtro rimosso con successo.']);
    }

    /**
     * Clona i filtri di un operatore sorgente su uno o più operatori di destinazione.
     * Supporta modalità 'replace' (sovrascrittura completa) e 'merge' (unione senza duplicati).
     */
    public function copy(Request $request): JsonResponse
    {
        $userFk = config('filterbymodel.user.foreign_key', 'user_id');

        $validated = $request->validate([
            'source_user_id'    => 'required',
            'target_user_ids'   => 'required|array|min:1',
            'target_user_ids.*' => 'required',
            'mode'              => 'sometimes|string|in:replace,merge',
        ]);

        $sourceUserId = $validated['source_user_id'];
        $targetUserIds = array_values(array_unique($validated['target_user_ids']));
        $mode = $validated['mode'] ?? 'replace';

        // Escludi l'utente sorgente se accidentalmente incluso nei target
        $targetUserIds = array_filter($targetUserIds, fn($id) => (string)$id !== (string)$sourceUserId);

        if (empty($targetUserIds)) {
            return response()->json([
                'message' => 'Seleziona almeno un utente di destinazione diverso da quello sorgente.',
            ], 422);
        }

        $sourceFilters = UserFilter::where($userFk, $sourceUserId)->get();

        if ($sourceFilters->isEmpty()) {
            return response()->json([
                'message' => "L'operatore sorgente non ha alcun filtro o competenza assegnata da clonare.",
            ], 422);
        }

        $createdCount = 0;

        \Illuminate\Support\Facades\DB::transaction(function () use ($sourceFilters, $targetUserIds, $userFk, $mode, &$createdCount) {
            foreach ($targetUserIds as $targetUserId) {
                if ($mode === 'replace') {
                    UserFilter::where($userFk, $targetUserId)->delete();
                }

                foreach ($sourceFilters as $sourceFilter) {
                    if ($mode === 'merge') {
                        $q = UserFilter::where($userFk, $targetUserId)
                            ->where('filterable_type', $sourceFilter->filterable_type)
                            ->where('filterable_id', $sourceFilter->filterable_id)
                            ->where('group', $sourceFilter->group);

                        if ($sourceFilter->target_model !== null) {
                            $q->where('target_model', $sourceFilter->target_model);
                        } else {
                            $q->whereNull('target_model');
                        }

                        if ($q->exists()) {
                            continue;
                        }
                    }

                    UserFilter::create([
                        $userFk            => $targetUserId,
                        'filterable_type'  => $sourceFilter->filterable_type,
                        'filterable_id'    => $sourceFilter->filterable_id,
                        'target_model'     => $sourceFilter->target_model,
                        'include_children' => $sourceFilter->include_children,
                        'parent_column'    => $sourceFilter->parent_column,
                        'group'            => $sourceFilter->group,
                    ]);

                    $createdCount++;
                }
            }
        });

        $targetsCount = count($targetUserIds);

        return response()->json([
            'message'       => "Competenze clonate con successo su {$targetsCount} operatore/i ({$createdCount} regole totali create).",
            'copied_count'  => $createdCount,
            'targets_count' => $targetsCount,
        ]);
    }

    /**
     * Ricerca utenti/operatori per l'autocomplete.
     * Supporta parametri dinamici per tabella, modello, campo ID e campo Label.
     * Utilizza la nuova configurazione multi-modello con display come array.
     */
    public function searchUsers(Request $request): JsonResponse
    {
        $query = (string) $request->input('q', '');
        $userType = $request->input('user_type');
        $tableName = $request->input('table');
        $modelClass = $request->input('model');
        $idField = $request->input('id_field');
        $labelField = $request->input('label_field', null);
        $limit = min((int) $request->input('limit', 20), 100);

        /** @var ModelFilterService $service */
        $service = app(ModelFilterService::class);

        // Risolvi la configurazione del modello utente
        $userConfig = $service->resolveUserModelConfig($userType ?: $modelClass);

        // Se non specificata la tabella, prova a ricavarla dal modello configurato o default 'users'
        $userModel = $modelClass ?: ($userConfig['class'] ?? config('filterbymodel.user.model', 'App\\Models\\User'));
        $resolvedIdField = $idField ?: ($userConfig['primary_key'] ?? config('filterbymodel.user.primary_key', 'id'));

        $dbQuery = null;
        $resolvedTable = $userConfig['table'] ?? config('filterbymodel.user.table', 'users');

        if (class_exists($userModel)) {
            $instance = new $userModel();
            $resolvedTable = $instance->getTable();
            $dbQuery = $instance->newQuery();
        } else {
            $resolvedTable = $tableName ?: $resolvedTable;
            $dbQuery = \Illuminate\Support\Facades\DB::table($resolvedTable);
        }

        if ($tableName && $tableName !== $resolvedTable) {
            $resolvedTable = $tableName;
            $dbQuery = \Illuminate\Support\Facades\DB::table($resolvedTable);
        }

        // Verifica colonne esistenti nella tabella
        $columns = [];
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable($resolvedTable)) {
                $columns = \Illuminate\Support\Facades\Schema::getColumnListing($resolvedTable);
            }
        } catch (\Throwable $e) {
            // Ignora se non è possibile leggere lo schema
        }

        // Filtro di ricerca se q è presente
        if (trim($query) !== '') {
            $searchableFields = (array) ($userConfig['searchable'] ?? config('filterbymodel.user.searchable_fields', [
                'name', 'cognome', 'nome', 'email', 'username', 'matricola', 'ragione_sociale', 'denominazione'
            ]));

            $dbQuery->where(function ($qBuilder) use ($query, $resolvedIdField, $labelField, $columns, $searchableFields) {
                // Ricerca su ID se numerico
                if (is_numeric($query) && (empty($columns) || in_array($resolvedIdField, $columns))) {
                    $qBuilder->orWhere($resolvedIdField, $query);
                }

                // Ricerca su label_field specificato (se passato)
                if ($labelField && (empty($columns) || in_array($labelField, $columns))) {
                    $qBuilder->orWhere($labelField, 'LIKE', "%{$query}%");
                }

                // Ricerca su tutti i campi configurati/trovati
                foreach ($searchableFields as $field) {
                    if (empty($columns) || in_array($field, $columns)) {
                        $qBuilder->orWhere($field, 'LIKE', "%{$query}%");
                    }
                }
            });
        }

        $results = $dbQuery->limit($limit)->get();

        // Mappa i risultati formattando i campi utente con la nuova configurazione display array
        $formatted = $results->map(function ($row) use ($service, $userConfig, $resolvedIdField) {
            return $service->formatUserDisplay($row, $userConfig, $resolvedIdField);
        });

        return response()->json($formatted);
    }

    /**
     * Restituisce gli elementi reali (ID e Descrizione) per un modello di competenza/criterio
     * (es. App\Models\Qualifica o App\Models\Ufficio) per l'autocompletamento rapido.
     */
    public function criteriaItems(Request $request): JsonResponse
    {
        $modelClass = (string) $request->input('scope_filter', '');
        $search = (string) $request->input('search', '');

        if (empty($modelClass) || !class_exists($modelClass)) {
            return response()->json(['items' => []]);
        }

        try {
            /** @var \Illuminate\Database\Eloquent\Model $instance */
            $instance = new $modelClass();
            $table = $instance->getTable();
            $pk = $instance->getKeyName() ?: 'id';

            if (!\Illuminate\Support\Facades\Schema::hasTable($table)) {
                return response()->json(['items' => []]);
            }

            $columns = \Illuminate\Support\Facades\Schema::getColumnListing($table);
            $configLimit = (int) config('filterbymodel.introspection.distinct_values_limit', 50);
            $searchLimit = (int) config('filterbymodel.introspection.search_limit', 25);
            $limit = !empty($search) ? $searchLimit : $configLimit;

            $query = $instance->newQuery();

            if (!empty($search)) {
                $searchableFields = ['denominazione', 'nome', 'descrizione', 'titolo', 'name', 'label', 'codice', 'ragione_sociale', 'desc'];
                $existingSearchFields = array_intersect($searchableFields, $columns);

                $query->where(function ($qBuilder) use ($search, $pk, $columns, $existingSearchFields) {
                    if (in_array($pk, $columns)) {
                        $qBuilder->orWhere($pk, 'LIKE', "%{$search}%");
                    }
                    foreach ($existingSearchFields as $field) {
                        $qBuilder->orWhere($field, 'LIKE', "%{$search}%");
                    }
                });
            }

            $results = $query->limit($limit)->get();

            /** @var ModelFilterService $service */
            $service = app(ModelFilterService::class);

            // Crea una configurazione generica per formattare le righe del criterio
            $criteriaConfig = [
                'primary_key' => $pk,
                'display'     => array_values(array_intersect(['denominazione', 'nome', 'descrizione', 'name', 'label', 'titolo'], $columns)),
                'separator'   => ' — ',
                'subtext'     => array_values(array_intersect(['codice', 'email', 'ragione_sociale'], $columns)),
            ];

            // Fallback se nessun campo display è trovato
            if (empty($criteriaConfig['display'])) {
                $criteriaConfig['display'] = [$pk];
            }

            $items = $results->map(function ($row) use ($service, $criteriaConfig, $pk) {
                $formatted = $service->formatUserDisplay($row, $criteriaConfig, $pk);
                $id = $formatted['id'];
                $label = $formatted['label'];
                $sublabel = ($formatted['sublabel'] !== "ID: {$id}") ? $formatted['sublabel'] : '';

                $displayText = "ID: {$id} — {$label}";
                if ($sublabel) {
                    $displayText .= " ({$sublabel})";
                }

                return [
                    'id'       => (string) $id,
                    'label'    => $label,
                    'sublabel' => $sublabel,
                    'display'  => $displayText,
                ];
            })->values();

            return response()->json(['items' => $items]);
        } catch (\Throwable $e) {
            return response()->json(['items' => []]);
        }
    }

    /**
     * Resoconto e riepilogo di tutti gli utenti con lo stato dei loro permessi/filtri bindati.
     * Include conteggi totali, filtri per stato (con permessi / senza permessi) e ricerca.
     * Ora include anche lo stato del bypass globale per ciascun utente.
     */
    public function summary(Request $request): JsonResponse
    {
        $query = (string) $request->input('q', '');
        $statusFilter = $request->input('status', 'all'); // 'all', 'with_filters', 'without_filters', 'bypassed'
        $userType = $request->input('user_type');
        $tableName = $request->input('table');
        $modelClass = $request->input('model');
        $idField = $request->input('id_field');
        $labelField = $request->input('label_field', null);
        $userFk = config('filterbymodel.user.foreign_key', 'user_id');

        /** @var ModelFilterService $service */
        $service = app(ModelFilterService::class);

        // Risolvi la configurazione del modello utente
        $userConfig = $service->resolveUserModelConfig($userType ?: $modelClass);

        $userModel = $modelClass ?: ($userConfig['class'] ?? config('filterbymodel.user.model', 'App\\Models\\User'));
        $resolvedIdField = $idField ?: ($userConfig['primary_key'] ?? config('filterbymodel.user.primary_key', 'id'));
        $resolvedUserType = $userType ?: $userModel;

        $dbQuery = null;
        $resolvedTable = $userConfig['table'] ?? config('filterbymodel.user.table', 'users');

        if (class_exists($userModel)) {
            $instance = new $userModel();
            $resolvedTable = $instance->getTable();
            $dbQuery = $instance->newQuery();
        } else {
            $resolvedTable = $tableName ?: $resolvedTable;
            $dbQuery = \Illuminate\Support\Facades\DB::table($resolvedTable);
        }

        if ($tableName && $tableName !== $resolvedTable) {
            $resolvedTable = $tableName;
            $dbQuery = \Illuminate\Support\Facades\DB::table($resolvedTable);
        }

        // Verifica colonne esistenti
        $columns = [];
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable($resolvedTable)) {
                $columns = \Illuminate\Support\Facades\Schema::getColumnListing($resolvedTable);
            }
        } catch (\Throwable $e) {}

        // Ricerca per testo (se presente)
        if (trim($query) !== '') {
            $searchableFields = (array) ($userConfig['searchable'] ?? config('filterbymodel.user.searchable_fields', [
                'name', 'cognome', 'nome', 'email', 'username', 'matricola', 'ragione_sociale', 'denominazione'
            ]));

            $dbQuery->where(function ($qBuilder) use ($query, $resolvedIdField, $labelField, $columns, $searchableFields) {
                if (is_numeric($query) && (empty($columns) || in_array($resolvedIdField, $columns))) {
                    $qBuilder->orWhere($resolvedIdField, $query);
                }
                if ($labelField && (empty($columns) || in_array($labelField, $columns))) {
                    $qBuilder->orWhere($labelField, 'LIKE', "%{$query}%");
                }
                foreach ($searchableFields as $field) {
                    if (empty($columns) || in_array($field, $columns)) {
                        $qBuilder->orWhere($field, 'LIKE', "%{$query}%");
                    }
                }
            });
        }

        $allUsers = $dbQuery->get();

        // Recupera tutti i filtri utente esistenti
        $allUserFilters = UserFilter::all()->groupBy($userFk);

        // Recupera tutti gli utenti con bypass attivo
        $bypassTable = config('filterbymodel.tables.filter_user_bypasses', 'filter_user_bypasses');
        $bypassedUserIds = [];
        try {
            $bypassedUserIds = \Illuminate\Support\Facades\DB::table($bypassTable)
                ->where('user_type', $resolvedUserType)
                ->pluck('user_id')
                ->toArray();
        } catch (\Throwable $e) {
            // Tabella non ancora creata
        }

        // Super-utenti hardcodati
        $superUserIds = (array) config('filterbymodel.users.super_user_ids', []);

        $totalUsers = $allUsers->count();
        $totalWithFilters = 0;
        $totalWithoutFilters = 0;
        $totalBypassed = 0;

        $items = [];

        foreach ($allUsers as $row) {
            $formattedUser = $service->formatUserDisplay($row, $userConfig, $resolvedIdField);
            $id = $formattedUser['id'];

            $userFilters = $allUserFilters->get($id, collect());
            $hasFilters = $userFilters->isNotEmpty();
            $filtersCount = $userFilters->count();
            $groupsCount = $userFilters->pluck('group')->unique()->count();

            // Stato bypass
            $isBypassed = in_array($id, $bypassedUserIds, false) || in_array($id, $superUserIds, false);

            if ($isBypassed) {
                $totalBypassed++;
            }

            if ($hasFilters) {
                $totalWithFilters++;
            } else {
                $totalWithoutFilters++;
            }

            // Filtro per stato se richiesto
            if ($statusFilter === 'with_filters' && !$hasFilters) {
                continue;
            }
            if ($statusFilter === 'without_filters' && $hasFilters) {
                continue;
            }
            if ($statusFilter === 'bypassed' && !$isBypassed) {
                continue;
            }

            // Raggruppa i filtri per tipo di modello
            $byType = $userFilters->groupBy('filterable_type')->map(function ($group, $type) {
                return [
                    'type'  => $type,
                    'name'  => class_basename($type),
                    'count' => $group->count(),
                ];
            })->values();

            $items[] = array_merge($formattedUser, [
                'has_filters'     => $hasFilters,
                'filters_count'   => $filtersCount,
                'groups_count'    => $groupsCount,
                'is_bypassed'     => $isBypassed,
                'summaryBadges'   => $byType,
                'filters_summary' => $byType,
            ]);
        }

        return response()->json([
            'data'                  => $items,
            'total_users'           => $totalUsers,
            'total_with_filters'    => $totalWithFilters,
            'total_without_filters' => $totalWithoutFilters,
            'total_bypassed'        => $totalBypassed,
        ]);
    }

    /**
     * Restituisce lo stato di bypass globale per un utente specifico.
     */
    public function bypassStatus(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id'   => 'required',
            'user_type' => 'nullable|string|max:255',
        ]);

        $userId = $validated['user_id'];
        $userType = $validated['user_type'] ?? config('filterbymodel.user.model', 'App\Models\User');

        /** @var ModelFilterService $service */
        $service = app(ModelFilterService::class);
        $isBypassed = $service->isUserBypassed((int) $userId, $userType);

        // Recupera anche la riga dal database (se esiste) per mostrare la motivazione
        $bypassRecord = null;
        $bypassTable = config('filterbymodel.tables.filter_user_bypasses', 'filter_user_bypasses');
        try {
            $bypassRecord = \Illuminate\Support\Facades\DB::table($bypassTable)
                ->where('user_id', $userId)
                ->where('user_type', $userType)
                ->first();
        } catch (\Throwable $e) {}

        $isHardcoded = in_array((int) $userId, (array) config('filterbymodel.users.super_user_ids', []), false);

        return response()->json([
            'is_bypassed'   => $isBypassed,
            'is_hardcoded'  => $isHardcoded,
            'reason'        => $bypassRecord->reason ?? null,
            'created_at'    => $bypassRecord->created_at ?? null,
        ]);
    }

    /**
     * Attiva o disattiva il bypass globale per un utente specifico.
     */
    public function toggleBypass(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id'   => 'required',
            'user_type' => 'nullable|string|max:255',
            'enabled'   => 'required|boolean',
            'reason'    => 'nullable|string|max:255',
        ]);

        $userId = $validated['user_id'];
        $userType = $validated['user_type'] ?? config('filterbymodel.user.model', 'App\Models\User');
        $enabled = (bool) $validated['enabled'];
        $reason = $validated['reason'] ?? null;

        // Impedisci la disattivazione di un bypass hardcodato
        $isHardcoded = in_array((int) $userId, (array) config('filterbymodel.users.super_user_ids', []), false);
        if ($isHardcoded && !$enabled) {
            return response()->json([
                'message' => 'Impossibile disattivare il bypass: questo utente è definito come super-utente nella configurazione del sistema.',
            ], 422);
        }

        if ($enabled) {
            // Attiva il bypass
            UserBypass::updateOrCreate(
                ['user_type' => $userType, 'user_id' => $userId],
                ['reason' => $reason]
            );

            return response()->json([
                'message'     => 'Bypass globale attivato. L\'utente ha ora accesso illimitato a tutti i dati.',
                'is_bypassed' => true,
            ]);
        } else {
            // Disattiva il bypass
            $bypassTable = config('filterbymodel.tables.filter_user_bypasses', 'filter_user_bypasses');
            \Illuminate\Support\Facades\DB::table($bypassTable)
                ->where('user_id', $userId)
                ->where('user_type', $userType)
                ->delete();

            return response()->json([
                'message'     => 'Bypass globale disattivato. L\'utente è ora soggetto ai filtri perimetrali.',
                'is_bypassed' => false,
            ]);
        }
    }

    /**
     * Restituisce l'elenco dei modelli utente disponibili (configurati nella sezione users.models).
     */
    public function availableUserModels(): JsonResponse
    {
        $modelsConfig = config('filterbymodel.users.models', []);

        $result = [];
        foreach ($modelsConfig as $class => $config) {
            $result[] = [
                'class' => $class,
                'label' => $config['label'] ?? class_basename($class),
            ];
        }

        // Se nessun modello è configurato, restituisci il modello utente di default
        if (empty($result)) {
            $result[] = [
                'class' => config('filterbymodel.user.model', 'App\Models\User'),
                'label' => 'Utenti',
            ];
        }

        return response()->json($result);
    }
}
