<?php

namespace SalvatoreCervone\FilterByModel\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class UserFilter extends Model
{
    /**
     * Attributi assegnabili in massa.
     */
    protected $fillable = [
        'user_id',
        'filterable_type',
        'filterable_id',
        'include_children',
        'parent_column',
        'group',
    ];

    /**
     * Cast automatici dei tipi.
     */
    protected $casts = [
        'include_children' => 'boolean',
        'group'            => 'integer',
    ];

    /**
     * Restituisce dinamicamente il nome della tabella dalla configurazione.
     */
    public function getTable(): string
    {
        return config('filterbymodel.tables.user_filters', 'user_filters');
    }

    /**
     * Relazione polimorfica verso l'entità filtrata.
     */
    public function filterable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Relazione dinamica verso il modello User configurato nell'applicazione.
     */
    public function user(): BelongsTo
    {
        $userModel = config('filterbymodel.user.model', config('auth.providers.users.model', 'App\Models\User'));
        $foreignKey = config('filterbymodel.user.foreign_key', 'user_id');

        return $this->belongsTo($userModel, $foreignKey);
    }
}
