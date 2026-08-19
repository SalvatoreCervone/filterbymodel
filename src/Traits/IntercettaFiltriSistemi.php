<?php

namespace SalvatoreCervone\FilterByModel\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;
use SalvatoreCervone\FilterByModel\Services\ModelFilterService;

/**
 * Trait IntercettaFiltriSistemi
 *
 * Applica automaticamente i filtri di sicurezza perimetrale ai modelli Eloquent.
 * Intercetta le operazioni di lettura (Global Scope), scrittura (saving) e cancellazione (deleting).
 *
 * Utilizzo: aggiungere `use IntercettaFiltriSistemi;` nel modello Eloquent da proteggere.
 */
trait IntercettaFiltriSistemi
{
    /**
     * Inizializzazione del Trait sui modelli Eloquent protetti.
     * Intercetta in automatico le operazioni di lettura (Scope), scrittura e cancellazione.
     */
    protected static function bootIntercettaFiltriSistemi(): void
    {
        $scopeName = config('filterbymodel.security.global_scope_name', 'filter_by_model_security_perimeter');

        static::addGlobalScope($scopeName, function (Builder $builder) {
            $service = app(ModelFilterService::class);
            if ($service->resolveUserId() !== null) {
                $service->applicaFiltroQuery($builder);
            }
        });

        // Intercettazione in Scrittura (Salvataggio o Aggiornamento)
        static::saving(function (Model $model) {
            $model->validaPerimetroSicurezza();
        });

        // Intercettazione in Cancellazione
        static::deleting(function (Model $model) {
            $model->validaPerimetroSicurezza();
        });
    }

    /**
     * Valida i permessi del record corrente gestendo le logiche a Gruppi AND/OR.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function validaPerimetroSicurezza(): void
    {
        app(ModelFilterService::class)->validaPerimetroSicurezzaRecord($this);
    }
}
