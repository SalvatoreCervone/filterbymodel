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
        return config('filterbymodel.tables.filter_user_bypasses', 'filter_user_bypasses');
    }

    /**
     * Crea la tabella dei bypass globali per gli utenti.
     * Un utente con bypass attivo scavalca tutti i filtri perimetrali e accede a tutti i dati.
     */
    public function up(): void
    {
        $tableName = $this->getTableName();

        Schema::create($tableName, function (Blueprint $table) {
            $table->id();

            // Supporto multi-modello (es. App\Models\User, App\Models\Admin)
            $table->string('user_type')->default('App\\Models\\User')
                  ->comment('Classe del modello utente (supporto multi-modello).');

            $table->unsignedBigInteger('user_id')->index()
                  ->comment('ID dell\'utente con bypass attivo.');

            // Motivazione opzionale (es. "Amministratore Delegato", "Auditor", "Super Admin")
            $table->string('reason')->nullable()
                  ->comment('Motivazione del bypass (es. Amministratore Delegato, Auditor).');

            $table->timestamps();

            // Un utente può avere al massimo un bypass attivo per tipo
            $table->unique(['user_type', 'user_id'], 'filter_user_bypasses_unique');
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
