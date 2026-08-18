<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Restituisce il nome della tabella configurato.
     */
    protected function getTableName(): string
    {
        return config('filterbymodel.tables.filter_definitions', 'filter_definitions');
    }

    /**
     * Crea la tabella delle definizioni di filtro in modo completamente dinamico.
     */
    public function up(): void
    {
        $tableName = $this->getTableName();

        if (!Schema::hasTable($tableName)) {
            Schema::create($tableName, function (Blueprint $table) {
                $table->id();

                // Chi subisce il filtro (es. 'App\Models\Invoice' o 'App\Models\Customer')
                $table->string('model_class');

                // L'oggetto del filtro (es. 'App\Models\Branch' o 'App\Models\Department')
                $table->string('scope_filter');

                // Campi per la relazione Pivot (N:M) - NULL se relazione diretta (1:N)
                $table->string('pivot_table')->nullable()
                      ->comment('Nome della tabella pivot. NULL se relazione diretta.');
                $table->string('pivot_foreign_key')->nullable()
                      ->comment('FK della risorsa target sulla pivot.');
                $table->string('target_foreign_key')->nullable()
                      ->comment('Colonna sulla tabella target usata per il collegamento (se vuota usa la PK).');

                // Colonna del codice del filtro (usata sia in caso diretto sia pivot)
                $table->string('filter_key')
                      ->comment('Colonna filtro sulla pivot o sulla tabella diretta.');

                // Colonna personalizzata per la gerarchia ad albero
                $table->string('parent_column')->nullable()
                      ->comment('Nome della colonna per la gerarchia ad albero (es. padre_id, parent_id).');

                // Condizioni extra opzionali in formato JSON
                $table->json('additional_where')->nullable()
                      ->comment('Condizioni di filtraggio aggiuntive in formato JSON.');

                $table->timestamps();

                $table->unique(['model_class', 'scope_filter']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists($this->getTableName());
    }
};
