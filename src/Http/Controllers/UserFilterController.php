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
}
