<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>FilterByModel - Dashboard Amministrativa</title>
  
  <!-- CDN Tailwind CSS & Vue 3 -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
  
  <!-- Font Typography -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
  
  <style>
    body { font-family: 'Inter', sans-serif; }
    code, pre, .font-mono { font-family: 'JetBrains Mono', monospace; }
    [v-cloak] { display: none; }
  </style>
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
