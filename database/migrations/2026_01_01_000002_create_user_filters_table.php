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
        return config('filterbymodel.tables.user_filters', 'user_filters');
    }

    /**
     * Restituisce il nome della foreign key utente.
     */
    protected function getUserForeignKey(): string
    {
        return config('filterbymodel.user.foreign_key', 'user_id');
    }

    /**
     * Crea la tabella dei filtri utente in modo dinamico.
     */
    public function up(): void
    {
        $tableName = $this->getTableName();
        $userFk = $this->getUserForeignKey();

        Schema::create($tableName, function (Blueprint $table) use ($userFk) {
            $table->id();

            $table->unsignedBigInteger($userFk)->index();

            // Relazione polimorfica: filterable_type + filterable_id
            $table->morphs('filterable');

            // Risoluzione gerarchica ad albero
            $table->boolean('include_children')->default(false);
            $table->string('parent_column')->nullable()
                  ->comment('Colonna personalizzata padre per questo filtro (es. parent_id, padre_id).');

            // Gruppo logico: AND nello stesso gruppo, OR tra gruppi diversi
            $table->integer('group')->default(1);

            $table->timestamps();

            // Indice composito dinamico
            $table->index([$userFk, 'filterable_type'], 'user_filters_lookup_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists($this->getTableName());
    }
};
