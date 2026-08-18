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
        $service = app(ModelFilterService::class);

        // Se non vi è un utente autenticato / risolvibile, non applica il vincolo
        if ($service->resolveUserId() === null) {
            return;
        }

        // Rileva dinamicamente la classe del modello corrente che utilizza il Trait
        $modelClass = static::class;
        $resolvedGroups = $service->ottieniFiltriRisolti($modelClass);

        if (empty($resolvedGroups)) {
            return; // Se non sono stati configurati filtri per questo modello a DB, l'utente ha accesso
        }

        $almenoUnGruppoEValido = false;

        // Esegue la logica OR tra i vari gruppi impostati
        foreach ($resolvedGroups as $groupId => $filtersByType) {
            if ($service->verificaRecord($this, $filtersByType)) {
                $almenoUnGruppoEValido = true;
                break; // Un gruppo valido è sufficiente per autorizzare l'operazione
            }
        }

        if (!$almenoUnGruppoEValido) {
            $errorMessage = config(
                'filterbymodel.security.unauthorized_message',
                'Operazione bloccata. Non possiedi i requisiti di competenza necessari per interagire con questa risorsa.'
            );

            throw ValidationException::withMessages([
                'authorization' => $errorMessage,
            ]);
        }
    }
}
