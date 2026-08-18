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
}
