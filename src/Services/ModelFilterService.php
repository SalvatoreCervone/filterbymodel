<?php

namespace SalvatoreCervone\FilterByModel\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use ReflectionClass;
use RuntimeException;
use SalvatoreCervone\FilterByModel\Models\FilterDefinition;
use SalvatoreCervone\FilterByModel\Models\UserFilter;

class ModelFilterService
{
    /**
     * Risolve l'ID dell'utente da autorizzare.
     */
    public function resolveUserId(?int $userId = null): ?int
    {
        if ($userId !== null) {
            return $userId;
        }

        $resolver = config('filterbymodel.security.auth_id_resolver');
        if (is_callable($resolver)) {
            return call_user_func($resolver);
        }

        return Auth::check() ? (int) Auth::id() : null;
    }

    /**
     * Risolve e prepara la struttura dei filtri autorizzati per il modello dato.
     *
     * @param string   $modelClass  FQCN del modello Eloquent protetto
     * @param int|null $userId      ID utente (default: utente autenticato / risolto dinamicamente)
     * @return array   Struttura raggruppata dei filtri risolti
     */
    public function ottieniFiltriRisolti(string $modelClass, ?int $userId = null): array
    {
        $userId = $this->resolveUserId($userId);

        if ($userId === null) {
            return [];
        }

        $definitionsTable = config('filterbymodel.tables.filter_definitions', 'filter_definitions');
        $userFk = config('filterbymodel.user.foreign_key', 'user_id');

        $allowedDefinitions = DB::table($definitionsTable)
            ->where('model_class', $modelClass)
            ->get()
            ->keyBy('scope_filter');

        if ($allowedDefinitions->isEmpty()) {
            return [];
        }

        $userFilters = UserFilter::where($userFk, $userId)
            ->whereIn('filterable_type', $allowedDefinitions->keys())
            ->where(function ($query) use ($modelClass) {
                $query->whereNull('target_model')
                      ->orWhere('target_model', $modelClass);
            })
            ->get();

        if ($userFilters->isEmpty()) {
            return [];
        }

        $resolvedStructure = [];
        $groups = $userFilters->groupBy('group');

        foreach ($groups as $groupId => $filtersInGroup) {
            $filtersByType = $filtersInGroup->groupBy('filterable_type');

            foreach ($filtersByType as $type => $items) {
                $definition = $allowedDefinitions->get($type);

                if (!class_exists($type)) {
                    throw new RuntimeException(
                        "Errore di configurazione: Il modello di filtro '{$type}' non esiste a sistema."
                    );
                }

                if (!empty($definition->pivot_table) && empty($definition->pivot_foreign_key)) {
                    throw new RuntimeException(
                        "Configurazione errata: La tabella pivot '{$definition->pivot_table}' richiede la specifica di 'pivot_foreign_key'."
                    );
                }

                if (empty($definition->filter_key)) {
                    throw new RuntimeException(
                        "Configurazione incompleta: Manca il campo 'filter_key' nella definizione del filtro '{$type}'."
                    );
                }

                $dummyInstance = new $type;
                $baseTable = $dummyInstance->getTable();
                $parentColumn = !empty($definition->parent_column) 
                    ? $definition->parent_column 
                    : $this->resolveParentColumn($dummyInstance);

                $allowedIds = [];
                foreach ($items as $item) {
                    if (isset($item->filterable_id) && $item->filterable_id !== '') {
                        $allowedIds[] = is_numeric($item->filterable_id) ? (int) $item->filterable_id : (string) $item->filterable_id;

                        if ($item->include_children && is_numeric($item->filterable_id)) {
                            $actualParentCol = !empty($item->parent_column)
                                ? $item->parent_column
                                : $parentColumn;

                            $allowedIds = array_merge(
                                $allowedIds,
                                $this->getTreeChildrenIds($baseTable, (int) $item->filterable_id, null, $actualParentCol)
                            );
                        }
                    }
                }

                if (empty($allowedIds)) {
                    continue;
                }

                $additionalWhere = [];
                if (!empty($definition->additional_where)) {
                    $decoded = is_string($definition->additional_where)
                        ? json_decode($definition->additional_where, true)
                        : (array) $definition->additional_where;

                    if (is_string($definition->additional_where) && json_last_error() !== JSON_ERROR_NONE) {
                        throw new RuntimeException(
                            "Errore JSON: Il campo 'additional_where' contiene JSON non valido."
                        );
                    }
                    $additionalWhere = is_array($decoded) ? $decoded : [];
                }

                $resolvedStructure[$groupId][$type] = [
                    'pivot_table'        => !empty($definition->pivot_table) ? $definition->pivot_table : null,
                    'pivot_foreign_key'  => !empty($definition->pivot_foreign_key) ? $definition->pivot_foreign_key : null,
                    'target_foreign_key' => !empty($definition->target_foreign_key) ? $definition->target_foreign_key : null,
                    'filter_key'         => $definition->filter_key,
                    'additional_where'   => $additionalWhere,
                    'ids'                => array_values(array_unique($allowedIds)),
                ];
            }
        }

        return $resolvedStructure;
    }

    /**
     * Modifica la query di lettura Eloquent per applicare i perimetri di sicurezza autorizzati.
     */
    public function applicaFiltroQuery(Builder $builder): void
    {
        $modelClass = get_class($builder->getModel());
        $tableName = $builder->getModel()->getTable();
        $resolvedGroups = $this->ottieniFiltriRisolti($modelClass);

        if (empty($resolvedGroups)) {
            return;
        }

        $skipAdditional = isset($builder->skipAdditionalWheres) && $builder->skipAdditionalWheres === true;

        $builder->where(function (Builder $mainQuery) use ($resolvedGroups, $tableName, $builder, $skipAdditional) {
            $isFirstGroup = true;

            foreach ($resolvedGroups as $groupId => $filtersByType) {
                $whereMethod = $isFirstGroup ? 'where' : 'orWhere';
                $isFirstGroup = false;

                $mainQuery->{$whereMethod}(function (Builder $subQuery) use ($filtersByType, $tableName, $builder, $skipAdditional) {
                    foreach ($filtersByType as $type => $data) {
                        if (empty($data['pivot_table'])) {
                            // Relazione Diretta (1:N)
                            $colonnaFiltroCompleta = str_contains($data['filter_key'], '.')
                                ? $data['filter_key']
                                : $tableName . '.' . $data['filter_key'];

                            $subQuery->whereIn($colonnaFiltroCompleta, $data['ids']);

                            if (!$skipAdditional && !empty($data['additional_where'])) {
                                $this->applicaCondizioniAggiuntive($subQuery, $data['additional_where'], $tableName);
                            }
                        } else {
                            // Relazione Pivot (N:M)
                            $targetKey = !empty($data['target_foreign_key'])
                                ? $data['target_foreign_key']
                                : $builder->getModel()->getKeyName();

                            $subQuery->whereIn($tableName . '.' . $targetKey, function ($query) use ($data, $skipAdditional) {
                                $query->select($data['pivot_foreign_key'])
                                    ->from($data['pivot_table'])
                                    ->whereIn($data['filter_key'], $data['ids']);

                                if (!$skipAdditional && !empty($data['additional_where'])) {
                                    $this->applicaCondizioniAggiuntive($query, $data['additional_where']);
                                }
                            });
                        }
                    }
                });
            }
        });
    }

    /**
     * Valida se una singola istanza del modello soddisfa le regole prima di un salvataggio o di una cancellazione.
     */
    public function verificaRecord($modelInstance, array $filtersByType): bool
    {
        foreach ($filtersByType as $type => $data) {
            if (empty($data['pivot_table'])) {
                // Verifica relazione diretta
                $campoModello = $data['filter_key'];
                if (!in_array($modelInstance->{$campoModello}, $data['ids'])) {
                    return false;
                }

                if (!empty($data['additional_where'])) {
                    if (!$this->verificaRecordCondizioniAggiuntive($modelInstance, $data['additional_where'])) {
                        return false;
                    }
                }
            } else {
                // Verifica relazione pivot
                $recordKey = !empty($data['target_foreign_key'])
                    ? $modelInstance->{$data['target_foreign_key']}
                    : $modelInstance->getKey();

                $query = DB::table($data['pivot_table'])
                    ->where($data['pivot_foreign_key'], $recordKey)
                    ->whereIn($data['filter_key'], $data['ids']);

                if (!empty($data['additional_where'])) {
                    $this->applicaCondizioniAggiuntive($query, $data['additional_where']);
                }

                if (!$query->exists()) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Valida i permessi di un record per operazioni di scrittura o cancellazione.
     * Solleva una ValidationException se l'utente non possiede i permessi perimetrali.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function validaPerimetroSicurezzaRecord(Model $modelInstance): void
    {
        // Se non vi è un utente autenticato / risolvibile, non applica il vincolo
        if ($this->resolveUserId() === null) {
            return;
        }

        $modelClass = get_class($modelInstance);
        $resolvedGroups = $this->ottieniFiltriRisolti($modelClass);

        // Se non sono stati configurati filtri per questo modello a DB, l'utente ha accesso
        if (empty($resolvedGroups)) {
            return;
        }

        $almenoUnGruppoEValido = false;

        // Esegue la logica OR tra i vari gruppi impostati
        foreach ($resolvedGroups as $groupId => $filtersByType) {
            if ($this->verificaRecord($modelInstance, $filtersByType)) {
                $almenoUnGruppoEValido = true;
                break;
            }
        }

        if (!$almenoUnGruppoEValido) {
            $errorMessage = config(
                'filterbymodel.security.unauthorized_message',
                'Operazione bloccata. Non possiedi i requisiti di competenza necessari per interagire con questa risorsa.'
            );

            throw \Illuminate\Validation\ValidationException::withMessages([
                'authorization' => $errorMessage,
            ]);
        }
    }

    /**
     * Risolve dinamicamente il nome della colonna padre per la gerarchia con rilevamento intelligente.
     * Ordine di priorità:
     * 1. Metodo sul modello: $model->getParentColumnName()
     * 2. Proprietà sul modello: $model->parentColumn
     * 3. Config per-model: config("filterbymodel.hierarchy.model_columns.{ModelClass}")
     * 4. Auto-detection su Schema: verifica l'esistenza di colonne note ('padre_id', 'parent_id', 'id_padre', ecc.)
     * 5. Config globale: config('filterbymodel.hierarchy.parent_column', 'padre_id')
     */
    public function resolveParentColumn(Model $model): string
    {
        // 1. Metodo specifico sul modello
        if (method_exists($model, 'getParentColumnName')) {
            return $model->getParentColumnName();
        }

        // 2. Proprietà specifica sul modello
        if (isset($model->parentColumn) && is_string($model->parentColumn)) {
            return $model->parentColumn;
        }

        $modelClass = get_class($model);

        // 3. Mapping specifico per classe in configurazione
        $modelColumns = config('filterbymodel.hierarchy.model_columns', []);
        if (isset($modelColumns[$modelClass])) {
            return $modelColumns[$modelClass];
        }

        // 4. Auto-detection dinamico sullo schema del Database
        $tableName = $model->getTable();
        $candidates = config('filterbymodel.hierarchy.fallback_columns', [
            'padre_id',
            'parent_id',
            'id_padre',
            'parent_code',
            'id_genitore',
            'parent_node_id',
        ]);

        try {
            foreach ($candidates as $column) {
                if (\Illuminate\Support\Facades\Schema::hasColumn($tableName, $column)) {
                    return $column;
                }
            }
        } catch (\Throwable $e) {
            // In caso di problemi di connessione schema fallback al default
        }

        // 5. Fallback alla configurazione globale di default
        return config('filterbymodel.hierarchy.parent_column', 'padre_id');
    }

    /**
     * Metodo ricorsivo ottimizzato: carica la mappa dell'albero in memoria con 1 sola query.
     */
    private function getTreeChildrenIds(string $tableName, int $parentId, ?array $allRows = null, string $parentColumn = 'padre_id'): array
    {
        if ($allRows === null) {
            $allRows = DB::table($tableName)->select('id', $parentColumn)->get()->toArray();
        }

        $childrenIds = [];
        foreach ($allRows as $row) {
            if (isset($row->{$parentColumn}) && (int) $row->{$parentColumn} === $parentId) {
                $childrenIds[] = (int) $row->id;
                $childrenIds = array_merge(
                    $childrenIds,
                    $this->getTreeChildrenIds($tableName, (int) $row->id, $allRows, $parentColumn)
                );
            }
        }

        return $childrenIds;
    }

    /**
     * Rileva dinamicamente i modelli disponibili per la configurazione dei filtri.
     * Di default scansiona automaticamente la cartella App\Models (e app_path se necessario),
     * supporta percorsi personalizzati, classi da ignorare e aggiunta di modelli espliciti.
     */
    public function getAvailableModels(): array
    {
        $models = [];
        $ignoredClasses = config('filterbymodel.models.ignore', [
            \SalvatoreCervone\FilterByModel\Models\FilterDefinition::class,
            \SalvatoreCervone\FilterByModel\Models\UserFilter::class,
        ]);

        // 1. Auto-discovery abilitato di default
        if (config('filterbymodel.models.auto_discover', true)) {
            $discovered = $this->autoDiscoverModels();
            foreach ($discovered as $model) {
                if (!in_array($model['class'], $ignoredClasses, true)) {
                    $models[$model['class']] = $model;
                }
            }
        }

        // 2. Modelli espliciti definiti in configurazione (se presenti, vengono uniti)
        $explicit = config('filterbymodel.models.explicit', []);
        
        // Supporta anche il vecchio formato dove 'models' era direttamente un array di classi
        if (empty($explicit) && is_array(config('filterbymodel.models')) && isset(config('filterbymodel.models')[0]['class'])) {
            $explicit = config('filterbymodel.models');
        }

        if (is_array($explicit)) {
            foreach ($explicit as $item) {
                if (isset($item['class']) && !in_array($item['class'], $ignoredClasses, true)) {
                    $name = $item['name'] ?? class_basename($item['class']);
                    $table = null;
                    $primaryKey = 'id';
                    $foreignKey = null;

                    if (class_exists($item['class'])) {
                        try {
                            $instance = new $item['class']();
                            $table = $instance->getTable();
                            $primaryKey = $instance->getKeyName();
                            $foreignKey = $instance->getForeignKey();
                        } catch (\Throwable $e) {
                            $table = \Illuminate\Support\Str::snake(\Illuminate\Support\Str::pluralStudly($name));
                            $foreignKey = \Illuminate\Support\Str::snake($name) . '_id';
                        }
                    }

                    $models[$item['class']] = [
                        'class'       => $item['class'],
                        'name'        => $name,
                        'table'       => $table,
                        'primary_key' => $primaryKey,
                        'foreign_key' => $foreignKey,
                        'snake_name'  => \Illuminate\Support\Str::snake($name),
                    ];
                }
            }
        }

        // Ordina alfabeticamente per nome del modello
        $result = array_values($models);
        usort($result, fn($a, $b) => strcasecmp($a['name'], $b['name']));

        return $result;
    }

    /**
     * Scansiona i percorsi dell'applicazione per trovare automaticamente tutte le classi Eloquent Model.
     */
    public function autoDiscoverModels(): array
    {
        // Percorsi di default: prima app_path('Models'), poi app_path() se la cartella Models non esiste
        $defaultPaths = [];
        if (is_dir(app_path('Models'))) {
            $defaultPaths[] = app_path('Models');
        }
        if (is_dir(app_path()) && empty($defaultPaths)) {
            $defaultPaths[] = app_path();
        }

        $paths = config('filterbymodel.models.paths', $defaultPaths);
        $models = [];

        foreach ((array) $paths as $path) {
            if (!is_dir($path)) {
                continue;
            }

            $files = File::allFiles($path);
            foreach ($files as $file) {
                // Considera solo file PHP
                if ($file->getExtension() !== 'php') {
                    continue;
                }

                $class = $this->getClassFromFile($file->getRealPath());

                if ($class && class_exists($class)) {
                    try {
                        $reflection = new ReflectionClass($class);

                        // Deve essere una sottoclasse di Eloquent Model, non astratta e non un Trait/Interface
                        if ($reflection->isSubclassOf(Model::class) && !$reflection->isAbstract()) {
                            $table = null;
                            $primaryKey = 'id';
                            $foreignKey = null;

                            try {
                                $instance = new $class();
                                $table = $instance->getTable();
                                $primaryKey = $instance->getKeyName();
                                $foreignKey = $instance->getForeignKey();
                            } catch (\Throwable $e) {
                                $table = \Illuminate\Support\Str::snake(\Illuminate\Support\Str::pluralStudly($reflection->getShortName()));
                                $foreignKey = \Illuminate\Support\Str::snake($reflection->getShortName()) . '_id';
                            }

                            $models[] = [
                                'class'       => $class,
                                'name'        => $reflection->getShortName(),
                                'table'       => $table,
                                'primary_key' => $primaryKey,
                                'foreign_key' => $foreignKey,
                                'snake_name'  => \Illuminate\Support\Str::snake($reflection->getShortName()),
                            ];
                        }
                    } catch (\Throwable $e) {
                        // Ignora eventuali classi non istanziabili o con errori di autoload
                    }
                }
            }
        }

        return $models;
    }

    /**
     * Estrae il Fully Qualified Class Name (FQCN) da un file PHP.
     */
    protected function getClassFromFile(string $filePath): ?string
    {
        $contents = @file_get_contents($filePath);
        if ($contents === false) {
            return null;
        }

        $namespace = null;
        $class = null;

        if (preg_match('/namespace\s+([^;]+);/', $contents, $matches)) {
            $namespace = trim($matches[1]);
        }

        if (preg_match('/class\s+([A-Za-z0-9_]+)/', $contents, $matches)) {
            $class = trim($matches[1]);
        }

        if ($namespace && $class) {
            return $namespace . '\\' . $class;
        }

        return null;
    }

    /**
     * Normalizza ed applica condizioni aggiuntive strutturate su una query (Builder o DB Query).
     */
    public function applicaCondizioniAggiuntive($query, array $additionalWhere, string $tablePrefix = ''): void
    {
        $normalized = $this->normalizzaCondizioniAggiuntive($additionalWhere);

        foreach ($normalized as $condition) {
            $column = $condition['column'] ?? null;
            if (empty($column)) {
                continue;
            }

            $colName = !empty($tablePrefix) && !str_contains($column, '.')
                ? $tablePrefix . '.' . $column
                : $column;

            $operator = strtoupper(trim($condition['operator'] ?? '='));
            $value = $this->risolviValoreDinamico($condition['value'] ?? null);

            switch ($operator) {
                case 'IS NULL':
                case 'NULL':
                    $query->whereNull($colName);
                    break;

                case 'IS NOT NULL':
                case 'NOT NULL':
                    $query->whereNotNull($colName);
                    break;

                case 'IN':
                    $values = is_array($value) ? $value : array_map('trim', explode(',', (string) $value));
                    $query->whereIn($colName, $values);
                    break;

                case 'NOT IN':
                    $values = is_array($value) ? $value : array_map('trim', explode(',', (string) $value));
                    $query->whereNotIn($colName, $values);
                    break;

                case 'BETWEEN':
                    $values = is_array($value) ? $value : array_map('trim', explode(',', (string) $value));
                    if (count($values) >= 2) {
                        $query->whereBetween($colName, [$values[0], $values[1]]);
                    }
                    break;

                case 'NOT BETWEEN':
                    $values = is_array($value) ? $value : array_map('trim', explode(',', (string) $value));
                    if (count($values) >= 2) {
                        $query->whereNotBetween($colName, [$values[0], $values[1]]);
                    }
                    break;

                case 'LIKE':
                case 'NOT LIKE':
                case '!=':
                case '<>':
                case '>':
                case '>=':
                case '<':
                case '<=':
                case '=':
                default:
                    if ($value === null) {
                        if ($operator === '!=' || $operator === '<>') {
                            $query->whereNotNull($colName);
                        } else {
                            $query->whereNull($colName);
                        }
                    } else {
                        $query->where($colName, $operator, $value);
                    }
                    break;
            }
        }
    }

    /**
     * Verifica in-memory se un'istanza del record rispetta le condizioni aggiuntive.
     */
    public function verificaRecordCondizioniAggiuntive($modelInstance, array $additionalWhere): bool
    {
        $normalized = $this->normalizzaCondizioniAggiuntive($additionalWhere);

        foreach ($normalized as $condition) {
            $column = $condition['column'] ?? null;
            if (empty($column)) {
                continue;
            }

            $operator = strtoupper(trim($condition['operator'] ?? '='));
            $expectedValue = $this->risolviValoreDinamico($condition['value'] ?? null);
            $actualValue = $modelInstance->{$column} ?? null;

            switch ($operator) {
                case 'IS NULL':
                case 'NULL':
                    if ($actualValue !== null) {
                        return false;
                    }
                    break;

                case 'IS NOT NULL':
                case 'NOT NULL':
                    if ($actualValue === null) {
                        return false;
                    }
                    break;

                case 'IN':
                    $values = is_array($expectedValue) ? $expectedValue : array_map('trim', explode(',', (string) $expectedValue));
                    if (!in_array((string) $actualValue, array_map('strval', $values), true)) {
                        return false;
                    }
                    break;

                case 'NOT IN':
                    $values = is_array($expectedValue) ? $expectedValue : array_map('trim', explode(',', (string) $expectedValue));
                    if (in_array((string) $actualValue, array_map('strval', $values), true)) {
                        return false;
                    }
                    break;

                case 'BETWEEN':
                    $values = is_array($expectedValue) ? $expectedValue : array_map('trim', explode(',', (string) $expectedValue));
                    if (count($values) >= 2 && ($actualValue < $values[0] || $actualValue > $values[1])) {
                        return false;
                    }
                    break;

                case 'NOT BETWEEN':
                    $values = is_array($expectedValue) ? $expectedValue : array_map('trim', explode(',', (string) $expectedValue));
                    if (count($values) >= 2 && ($actualValue >= $values[0] && $actualValue <= $values[1])) {
                        return false;
                    }
                    break;

                case '>':
                    if (!($actualValue > $expectedValue)) return false;
                    break;
                case '>=':
                    if (!($actualValue >= $expectedValue)) return false;
                    break;
                case '<':
                    if (!($actualValue < $expectedValue)) return false;
                    break;
                case '<=':
                    if (!($actualValue <= $expectedValue)) return false;
                    break;
                case '!=':
                case '<>':
                    if ($actualValue == $expectedValue) return false;
                    break;
                case 'LIKE':
                    $pattern = str_replace('%', '.*', preg_quote((string) $expectedValue, '/'));
                    if (!preg_match('/^' . $pattern . '$/i', (string) $actualValue)) return false;
                    break;
                case 'NOT LIKE':
                    $pattern = str_replace('%', '.*', preg_quote((string) $expectedValue, '/'));
                    if (preg_match('/^' . $pattern . '$/i', (string) $actualValue)) return false;
                    break;
                case '=':
                default:
                    if ($actualValue != $expectedValue) return false;
                    break;
            }
        }

        return true;
    }

    /**
     * Converte qualsiasi formato di additional_where (array di regole, lista di oggetti o mappa chiave-valore legacy)
     * in una struttura standardizzata di condizioni.
     */
    public function normalizzaCondizioniAggiuntive(array $rawConditions): array
    {
        $normalized = [];

        // Se è una mappa associativa legacy {"status": "active", "is_deleted": 0}
        $isAssociativeMap = !empty($rawConditions) && array_keys($rawConditions) !== range(0, count($rawConditions) - 1);

        if ($isAssociativeMap) {
            foreach ($rawConditions as $column => $value) {
                if (is_array($value) && isset($value['column'])) {
                    $normalized[] = $value;
                } else {
                    $normalized[] = [
                        'column'   => (string) $column,
                        'operator' => $value === null ? 'IS NULL' : '=',
                        'value'    => $value,
                    ];
                }
            }
            return $normalized;
        }

        // Se è già un array di condizioni strutturate [{"column": "stato", "operator": "=", "value": "attivo"}]
        foreach ($rawConditions as $item) {
            if (is_array($item)) {
                $column = $item['column'] ?? $item['field'] ?? null;
                if (!empty($column)) {
                    $normalized[] = [
                        'column'   => (string) $column,
                        'operator' => (string) ($item['operator'] ?? '='),
                        'value'    => $item['value'] ?? null,
                    ];
                }
            }
        }

        return $normalized;
    }

    /**
     * Risolve eventuali placeholder dinamici nel valore (@auth_id, @user.id, @current_year, @today, @null).
     */
    public function risolviValoreDinamico(mixed $value): mixed
    {
        if (!is_string($value)) {
            return $value;
        }

        $trimmed = trim($value);

        return match (strtolower($trimmed)) {
            '@auth_id', '@user.id', '@user_id' => $this->resolveUserId(),
            '@current_year', '@year'           => (int) date('Y'),
            '@today', '@now', '@current_date'  => date('Y-m-d'),
            '@null'                            => null,
            default                            => $value,
        };
    }
}
