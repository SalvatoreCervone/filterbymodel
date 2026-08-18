<?php

namespace SalvatoreCervone\FilterByModel\Models;

use Illuminate\Database\Eloquent\Model;

class FilterDefinition extends Model
{
    /**
     * Attributi assegnabili in massa.
     */
    protected $fillable = [
        'model_class',
        'scope_filter',
        'pivot_table',
        'pivot_foreign_key',
        'target_foreign_key',
        'filter_key',
        'parent_column',
        'additional_where',
    ];

    /**
     * Cast automatici dei tipi.
     */
    protected $casts = [
        'additional_where' => 'array',
    ];

    /**
     * Restituisce dinamicamente il nome della tabella dalla configurazione.
     */
    public function getTable(): string
    {
        return config('filterbymodel.tables.filter_definitions', 'filter_definitions');
    }
}
