<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>FilterByModel - Dashboard Amministrativa</title>
  
  <!-- CSS & Vue 3 Locali (Nessuna CDN esterna, Offline-Ready, Conforme GDPR) -->
  @if (file_exists(public_path('vendor/filterbymodel/css/filterbymodel.css')))
    <link rel="stylesheet" href="{{ asset('vendor/filterbymodel/css/filterbymodel.css') }}">
  @else
    <link rel="stylesheet" href="{{ route('filterbymodel.asset', ['path' => 'css/filterbymodel.css']) }}">
  @endif

  @if (file_exists(public_path('vendor/filterbymodel/js/vue.global.prod.js')))
    <script src="{{ asset('vendor/filterbymodel/js/vue.global.prod.js') }}"></script>
  @else
    <script src="{{ route('filterbymodel.asset', ['path' => 'js/vue.global.prod.js']) }}"></script>
  @endif
</head>
<body class="bg-slate-100/80 text-slate-800 antialiased min-h-screen">
  <div id="app" class="pb-16" v-cloak>
    
    {{-- Barra di Navigazione Superiore --}}
    @include('filterbymodel::partials.navbar')

    {{-- Notifiche Toast Fluttuanti --}}
    @include('filterbymodel::partials.toast')

    {{-- Scheda 1: Regole di Visibilità Modelli & Simulatore SQL Live --}}
    @include('filterbymodel::partials.tab-definitions')

    {{-- Scheda 2: Competenze Operatore & Autocomplete --}}
    @include('filterbymodel::partials.tab-users')

    {{-- Scheda 3: Resoconto Globale Permessi & Statistiche --}}
    @include('filterbymodel::partials.tab-summary')

    {{-- Modale per Clonazione/Copia Competenze tra Operatori --}}
    @include('filterbymodel::partials.modal-clone')

  </div>

  {{-- Script Applicativo Vue 3 Reattivo --}}
  @include('filterbymodel::partials.scripts')
</body>
</html>
