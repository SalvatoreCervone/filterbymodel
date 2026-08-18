<?php

namespace SalvatoreCervone\FilterByModel\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use SalvatoreCervone\FilterByModel\Models\UserFilter;

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
            'include_children' => 'sometimes|boolean',
            'parent_column'    => 'nullable|string|max:255',
            'group'            => 'required|integer|min:1',
        ]);

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
                        $exists = UserFilter::where($userFk, $targetUserId)
                            ->where('filterable_type', $sourceFilter->filterable_type)
                            ->where('filterable_id', $sourceFilter->filterable_id)
                            ->where('group', $sourceFilter->group)
                            ->exists();

                        if ($exists) {
                            continue;
                        }
                    }

                    UserFilter::create([
                        $userFk            => $targetUserId,
                        'filterable_type'  => $sourceFilter->filterable_type,
                        'filterable_id'    => $sourceFilter->filterable_id,
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
     */
    public function searchUsers(Request $request): JsonResponse
    {
        $query = (string) $request->input('q', '');
        $tableName = $request->input('table');
        $modelClass = $request->input('model');
        $idField = $request->input('id_field', 'id');
        $labelField = $request->input('label_field', 'name');
        $limit = min((int) $request->input('limit', 20), 100);

        // Se non specificata la tabella, prova a ricavarla dal modello configurato o default 'users'
        $userModel = $modelClass ?: config('filterbymodel.user.model', 'App\\Models\\User');

        $dbQuery = null;
        $resolvedTable = 'users';

        if (class_exists($userModel)) {
            $instance = new $userModel();
            $resolvedTable = $instance->getTable();
            $dbQuery = $instance->newQuery();
        } else {
            $resolvedTable = $tableName ?: 'users';
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
            $dbQuery->where(function ($qBuilder) use ($query, $idField, $labelField, $columns) {
                // Ricerca su ID se numerico
                if (is_numeric($query) && (empty($columns) || in_array($idField, $columns))) {
                    $qBuilder->orWhere($idField, $query);
                }

                // Ricerca su label_field specificato
                if (empty($columns) || in_array($labelField, $columns)) {
                    $qBuilder->orWhere($labelField, 'LIKE', "%{$query}%");
                }

                // Campi di fallback comuni
                $searchableFields = ['name', 'email', 'username', 'nome', 'cognome', 'denominazione', 'ragione_sociale'];
                foreach ($searchableFields as $field) {
                    if ($field !== $labelField && (empty($columns) || in_array($field, $columns))) {
                        $qBuilder->orWhere($field, 'LIKE', "%{$query}%");
                    }
                }
            });
        }

        $results = $dbQuery->limit($limit)->get();

        // Mappa i risultati garantendo id e label leggibile
        $formatted = $results->map(function ($row) use ($idField, $labelField) {
            $id = is_object($row) ? ($row->{$idField} ?? $row->id ?? null) : ($row[$idField] ?? $row['id'] ?? null);
            
            $label = '';
            if (is_object($row)) {
                $label = $row->{$labelField} ?? $row->name ?? $row->nome ?? $row->email ?? "Utente #{$id}";
                // Aggiungi cognome se presente e non già incluso
                if (isset($row->cognome) && $labelField === 'nome') {
                    $label = trim("{$row->nome} {$row->cognome}");
                }
                $sublabel = $row->email ?? $row->username ?? (isset($row->id) ? "ID: {$id}" : '');
            } else {
                $label = $row[$labelField] ?? $row['name'] ?? $row['nome'] ?? $row['email'] ?? "Utente #{$id}";
                $sublabel = $row['email'] ?? $row['username'] ?? "ID: {$id}";
            }

            return [
                'id'       => $id,
                'label'    => $label,
                'sublabel' => $sublabel !== $label ? $sublabel : '',
            ];
        });

        return response()->json($formatted);
    }

    /**
     * Resoconto e riepilogo di tutti gli utenti con lo stato dei loro permessi/filtri bindati.
     * Include conteggi totali, filtri per stato (con permessi / senza permessi) e ricerca.
     */
    public function summary(Request $request): JsonResponse
    {
        $query = (string) $request->input('q', '');
        $statusFilter = $request->input('status', 'all'); // 'all', 'with_filters', 'without_filters'
        $tableName = $request->input('table');
        $modelClass = $request->input('model');
        $idField = $request->input('id_field', 'id');
        $labelField = $request->input('label_field', 'name');
        $userFk = config('filterbymodel.user.foreign_key', 'user_id');

        $userModel = $modelClass ?: config('filterbymodel.user.model', 'App\\Models\\User');

        $dbQuery = null;
        $resolvedTable = 'users';

        if (class_exists($userModel)) {
            $instance = new $userModel();
            $resolvedTable = $instance->getTable();
            $dbQuery = $instance->newQuery();
        } else {
            $resolvedTable = $tableName ?: 'users';
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
            $dbQuery->where(function ($qBuilder) use ($query, $idField, $labelField, $columns) {
                if (is_numeric($query) && (empty($columns) || in_array($idField, $columns))) {
                    $qBuilder->orWhere($idField, $query);
                }
                if (empty($columns) || in_array($labelField, $columns)) {
                    $qBuilder->orWhere($labelField, 'LIKE', "%{$query}%");
                }
                $searchableFields = ['name', 'email', 'username', 'nome', 'cognome', 'denominazione'];
                foreach ($searchableFields as $field) {
                    if ($field !== $labelField && (empty($columns) || in_array($field, $columns))) {
                        $qBuilder->orWhere($field, 'LIKE', "%{$query}%");
                    }
                }
            });
        }

        $allUsers = $dbQuery->get();

        // Recupera tutti i filtri utente esistenti
        $allUserFilters = UserFilter::all()->groupBy($userFk);

        $totalUsers = $allUsers->count();
        $totalWithFilters = 0;
        $totalWithoutFilters = 0;

        $items = [];

        foreach ($allUsers as $row) {
            $id = is_object($row) ? ($row->{$idField} ?? $row->id ?? null) : ($row[$idField] ?? $row['id'] ?? null);

            $label = '';
            if (is_object($row)) {
                $label = $row->{$labelField} ?? $row->name ?? $row->nome ?? $row->email ?? "Utente #{$id}";
                if (isset($row->cognome) && $labelField === 'nome') {
                    $label = trim("{$row->nome} {$row->cognome}");
                }
                $sublabel = $row->email ?? $row->username ?? (isset($row->id) ? "ID: {$id}" : '');
            } else {
                $label = $row[$labelField] ?? $row['name'] ?? $row['nome'] ?? $row['email'] ?? "Utente #{$id}";
                $sublabel = $row['email'] ?? $row['username'] ?? "ID: {$id}";
            }

            $userFilters = $allUserFilters->get($id, collect());
            $hasFilters = $userFilters->isNotEmpty();
            $filtersCount = $userFilters->count();
            $groupsCount = $userFilters->pluck('group')->unique()->count();

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

            // Raggruppa i filtri per tipo di modello
            $byType = $userFilters->groupBy('filterable_type')->map(function ($group, $type) {
                return [
                    'type'  => $type,
                    'name'  => class_basename($type),
                    'count' => $group->count(),
                ];
            })->values();

            $items[] = [
                'id'              => $id,
                'label'           => $label,
                'sublabel'        => $sublabel !== $label ? $sublabel : '',
                'has_filters'     => $hasFilters,
                'filters_count'   => $filtersCount,
                'groups_count'    => $groupsCount,
                'filters_summary' => $byType,
            ];
        }

        return response()->json([
            'data'                  => $items,
            'total_users'           => $totalUsers,
            'total_with_filters'    => $totalWithFilters,
            'total_without_filters' => $totalWithoutFilters,
        ]);
    }
}
