<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Aggiunge la colonna 'operator' alle tabelle 'filter_definitions' e 'user_filters'.
     * Supporta '=', 'LIKE' (diretto) e 'LIKE_REVERSE' (controcorrente).
     */
    public function up(): void
    {
        $defTable = config('filterbymodel.tables.filter_definitions', 'filter_definitions');
        if (Schema::hasTable($defTable) && !Schema::hasColumn($defTable, 'operator')) {
            Schema::table($defTable, function (Blueprint $table) {
                $table->string('operator', 20)->default('=')->after('filter_key')
                      ->comment('Operatore di confronto per il match (=, LIKE, LIKE_REVERSE).');
            });
        }

        $userFilterTable = config('filterbymodel.tables.user_filters', 'user_filters');
        if (Schema::hasTable($userFilterTable) && !Schema::hasColumn($userFilterTable, 'operator')) {
            Schema::table($userFilterTable, function (Blueprint $table) {
                $table->string('operator', 20)->default('=')->after('filterable_id')
                      ->comment('Operatore di confronto per questa assegnazione (=, LIKE, LIKE_REVERSE).');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $defTable = config('filterbymodel.tables.filter_definitions', 'filter_definitions');
        if (Schema::hasTable($defTable) && Schema::hasColumn($defTable, 'operator')) {
            Schema::table($defTable, function (Blueprint $table) {
                $table->dropColumn('operator');
            });
        }

        $userFilterTable = config('filterbymodel.tables.user_filters', 'user_filters');
        if (Schema::hasTable($userFilterTable) && Schema::hasColumn($userFilterTable, 'operator')) {
            Schema::table($userFilterTable, function (Blueprint $table) {
                $table->dropColumn('operator');
            });
        }
    }
};
