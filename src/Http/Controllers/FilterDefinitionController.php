<?php

namespace SalvatoreCervone\FilterByModel\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use SalvatoreCervone\FilterByModel\Models\FilterDefinition;
use SalvatoreCervone\FilterByModel\Services\ModelFilterService;

class FilterDefinitionController extends Controller
{
    /**
     * Elenco di tutte le definizioni di filtro configurate.
     */
    public function index(): JsonResponse
    {
        $definitions = FilterDefinition::all();

        return response()->json($definitions);
    }

    /**
     * Crea una nuova definizione di filtro.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'model_class'        => 'required|string|max:255',
            'scope_filter'       => 'required|string|max:255',
            'pivot_table'        => 'nullable|string|max:255',
            'pivot_foreign_key'  => 'nullable|string|max:255',
            'target_foreign_key' => 'nullable|string|max:255',
            'filter_key'         => 'required|string|max:255',
            'parent_column'      => 'nullable|string|max:255',
            'additional_where'   => 'nullable',
        ]);

        // Gestione del campo additional_where: accetta sia stringa JSON che array
        if (isset($validated['additional_where']) && is_string($validated['additional_where'])) {
            $decoded = json_decode($validated['additional_where'], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $validated['additional_where'] = $decoded;
            }
        }

        // Pulizia campi pivot quando la relazione è diretta
        if (empty($validated['pivot_table'])) {
            $validated['pivot_table'] = null;
            $validated['pivot_foreign_key'] = null;
            $validated['target_foreign_key'] = null;
        }

        if (empty($validated['parent_column'])) {
            $validated['parent_column'] = null;
        }

        $definition = FilterDefinition::updateOrCreate(
            [
                'model_class'  => $validated['model_class'],
                'scope_filter' => $validated['scope_filter'],
            ],
            $validated
        );

        return response()->json(['data' => $definition], 201);
    }

    /**
     * Elimina una definizione di filtro.
     */
    public function destroy(int $id): JsonResponse
    {
        $definition = FilterDefinition::findOrFail($id);
        $definition->delete();

        return response()->json(['message' => 'Definizione rimossa con successo.']);
    }

    /**
     * Restituisce dinamicamente l'elenco dei modelli disponibili (scansionati o configurati).
     */
    public function availableModels(): JsonResponse
    {
        $service = app(ModelFilterService::class);
        $models = $service->getAvailableModels();

        return response()->json($models);
    }

    /**
     * Restituisce le colonne della tabella del modello o della tabella pivot indicata.
     */
    public function modelColumns(Request $request): JsonResponse
    {
        $modelClass = $request->query('model_class');
        $tableName = $request->query('table');
        $columns = [];

        // Se è specificata una tabella ponte (pivot), estrai prioritariamente i suoi campi
        if (!empty($tableName)) {
            try {
                if (\Illuminate\Support\Facades\Schema::hasTable($tableName)) {
                    $columns = \Illuminate\Support\Facades\Schema::getColumnListing($tableName);
                }
            } catch (\Throwable $e) {
                // Fallback graceful
            }
        }

        // Altrimenti, se non c'è una pivot, estrai le colonne del modello da proteggere
        if (empty($columns) && !empty($modelClass) && class_exists($modelClass)) {
            try {
                /** @var \Illuminate\Database\Eloquent\Model $instance */
                $instance = new $modelClass();
                $table = $instance->getTable();
                if (\Illuminate\Support\Facades\Schema::hasTable($table)) {
                    $columns = \Illuminate\Support\Facades\Schema::getColumnListing($table);
                }
            } catch (\Throwable $e) {
                // Fallback graceful
            }
        }

        return response()->json(['columns' => array_values(array_unique($columns))]);
    }

    /**
     * Restituisce i valori univoci campionati (o filtrati con ricerca live) per una specifica colonna.
     */
    public function columnValues(Request $request): JsonResponse
    {
        $modelClass = $request->query('model_class');
        $tableName = $request->query('table');
        $column = $request->query('column');
        $search = $request->query('search');

        if (empty($column)) {
            return response()->json(['values' => [], 'has_more' => false]);
        }

        // Sanificazione del nome colonna
        if (!preg_match('/^[a-zA-Z0-9_]+$/', (string) $column)) {
            return response()->json(['values' => [], 'has_more' => false], 400);
        }

        $table = null;
        if (!empty($tableName)) {
            $table = $tableName;
        } elseif (!empty($modelClass) && class_exists($modelClass)) {
            try {
                /** @var \Illuminate\Database\Eloquent\Model $instance */
                $instance = new $modelClass();
                $table = $instance->getTable();
            } catch (\Throwable $e) {
                return response()->json(['values' => [], 'has_more' => false]);
            }
        }

        if (empty($table) || !\Illuminate\Support\Facades\Schema::hasTable($table) || !\Illuminate\Support\Facades\Schema::hasColumn($table, $column)) {
            return response()->json(['values' => [], 'has_more' => false]);
        }

        // Esclusione campi sensibili
        $lowerCol = strtolower($column);
        if (str_contains($lowerCol, 'password') || str_contains($lowerCol, 'token') || str_contains($lowerCol, 'secret') || str_contains($lowerCol, 'hash')) {
            return response()->json(['values' => [], 'has_more' => false]);
        }

        $configLimit = (int) config('filterbymodel.introspection.distinct_values_limit', 50);
        $searchLimit = (int) config('filterbymodel.introspection.search_limit', 15);
        $limit = !empty($search) ? $searchLimit : $configLimit;

        try {
            $query = \Illuminate\Support\Facades\DB::table($table)
                ->select($column)
                ->whereNotNull($column);

            if (!empty($search)) {
                $query->where($column, 'LIKE', '%' . $search . '%');
            }

            // Chiede limit + 1 per rilevare se ci sono ulteriori valori (alta cardinalità)
            $rawValues = $query->distinct()
                ->limit($limit + 1)
                ->pluck($column)
                ->toArray();

            $hasMore = count($rawValues) > $limit;
            $values = array_slice($rawValues, 0, $limit);

            $formatted = array_map(function ($v) {
                if (is_bool($v)) return $v ? '1' : '0';
                return (string) $v;
            }, $values);

            return response()->json([
                'values'   => array_values(array_unique($formatted)),
                'has_more' => $hasMore,
                'limit'    => $limit,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['values' => [], 'has_more' => false]);
        }
    }
}
