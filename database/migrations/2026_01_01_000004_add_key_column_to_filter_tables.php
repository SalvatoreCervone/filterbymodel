<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Aggiunge la colonna 'key_column' alle tabelle 'filter_definitions' e 'user_filters'.
     */
    public function up(): void
    {
        $defTable = config('filterbymodel.tables.filter_definitions', 'filter_definitions');
        if (Schema::hasTable($defTable) && !Schema::hasColumn($defTable, 'key_column')) {
            Schema::table($defTable, function (Blueprint $table) {
                $table->string('key_column')->nullable()->after('parent_column')
                      ->comment('Nome della colonna identificativa del nodo per l\'albero (es. id, codice, uuid). Se NULL usa PK o id.');
            });
        }

        $userFilterTable = config('filterbymodel.tables.user_filters', 'user_filters');
        if (Schema::hasTable($userFilterTable) && !Schema::hasColumn($userFilterTable, 'key_column')) {
            Schema::table($userFilterTable, function (Blueprint $table) {
                $table->string('key_column')->nullable()->after('parent_column')
                      ->comment('Colonna personalizzata chiave del nodo per questo filtro (se null eredita dalla definizione o dal modello).');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $defTable = config('filterbymodel.tables.filter_definitions', 'filter_definitions');
        if (Schema::hasTable($defTable) && Schema::hasColumn($defTable, 'key_column')) {
            Schema::table($defTable, function (Blueprint $table) {
                $table->dropColumn('key_column');
            });
        }

        $userFilterTable = config('filterbymodel.tables.user_filters', 'user_filters');
        if (Schema::hasTable($userFilterTable) && Schema::hasColumn($userFilterTable, 'key_column')) {
            Schema::table($userFilterTable, function (Blueprint $table) {
                $table->dropColumn('key_column');
            });
        }
    }
};
