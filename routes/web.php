<?php

use Illuminate\Support\Facades\Route;
use SalvatoreCervone\FilterByModel\Http\Controllers\AdminDashboardController;

/*
|--------------------------------------------------------------------------
| Rotte Web del Pannello di Controllo FilterByModel
|--------------------------------------------------------------------------
|
| Questa rotta carica la Dashboard Amministrativa standalone per gestire
| visualmente le regole di visibilità e i perimetri degli operatori.
|
| Prefisso e middleware sono configurabili in config/filterbymodel.php (routes.web)
|
*/

Route::get('/', [AdminDashboardController::class, 'index'])->name('filterbymodel.dashboard');
Route::get('/assets/{path}', \SalvatoreCervone\FilterByModel\Http\Controllers\AssetController::class)->where('path', '.*')->name('filterbymodel.asset');
