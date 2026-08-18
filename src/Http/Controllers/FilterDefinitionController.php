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
}
