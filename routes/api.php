<?php

use Illuminate\Support\Facades\Route;
use SalvatoreCervone\FilterByModel\Http\Controllers\FilterDefinitionController;
use SalvatoreCervone\FilterByModel\Http\Controllers\UserFilterController;

/*
|--------------------------------------------------------------------------
| Rotte API del Package FilterByModel
|--------------------------------------------------------------------------
|
| Queste rotte gestiscono le operazioni CRUD per le definizioni di filtro
| (regole di visibilità) e i filtri assegnati agli utenti.
|
| Prefisso e middleware sono configurabili in config/filterbymodel.php
|
*/

// --- Definizioni di Filtro (Admin) ---
Route::get('/filter-definitions', [FilterDefinitionController::class, 'index']);
Route::post('/filter-definitions', [FilterDefinitionController::class, 'store']);
Route::delete('/filter-definitions/{id}', [FilterDefinitionController::class, 'destroy']);

// --- Modelli Disponibili ---
Route::get('/available-models', [FilterDefinitionController::class, 'availableModels']);

// --- Filtri Utente ---
Route::get('/search-users', [UserFilterController::class, 'searchUsers']);
Route::get('/user-filters', [UserFilterController::class, 'index']);
Route::post('/user-filters', [UserFilterController::class, 'store']);
Route::delete('/user-filters/{id}', [UserFilterController::class, 'destroy']);
