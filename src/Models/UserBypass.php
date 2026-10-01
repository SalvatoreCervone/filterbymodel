<?php

namespace SalvatoreCervone\FilterByModel\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class UserBypass extends Model
{
    /**
     * Attributi assegnabili in massa.
     */
    protected $fillable = [
        'user_type',
        'user_id',
        'reason',
    ];

    /**
     * Restituisce dinamicamente il nome della tabella dalla configurazione.
     */
    public function getTable(): string
    {
        return config('filterbymodel.tables.filter_user_bypasses', 'filter_user_bypasses');
    }

    /**
     * Relazione dinamica verso il modello User configurato nell'applicazione.
     * Supporta multi-modello tramite il campo user_type.
     */
    public function user(): MorphTo
    {
        return $this->morphTo('user', 'user_type', 'user_id');
    }
}
