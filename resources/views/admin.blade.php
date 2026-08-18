<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>FilterByModel - Dashboard Amministrativa</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Inter', sans-serif; }
    code, pre, .font-mono { font-family: 'JetBrains Mono', monospace; }
  </style>
</head>
<body class="bg-slate-100/80 text-slate-800 antialiased min-h-screen">
  <div id="app" class="pb-16" v-cloak>
    
    <!-- TOP NAVIGATION BAR -->
    <nav class="bg-white border-b border-slate-200 sticky top-0 z-50 shadow-xs">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between h-16">
        <div class="flex items-center gap-3">
          <div class="p-2 bg-indigo-600 text-white rounded-xl shadow-sm">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
            </svg>
          </div>
          <div>
            <span class="font-extrabold text-slate-900 text-base tracking-tight">FilterByModel</span>
            <span class="ml-2 text-xs font-semibold px-2 py-0.5 bg-indigo-50 text-indigo-700 rounded-full border border-indigo-100">Pannello Admin</span>
          </div>
        </div>

        <div class="flex items-center gap-1 bg-slate-100 p-1 rounded-xl border border-slate-200">
          <button 
            @click="currentView = 'admin'"
            :class="[currentView === 'admin' ? 'bg-white text-indigo-700 shadow-sm font-bold' : 'text-slate-600 hover:text-slate-900 font-medium']"
            class="px-3.5 py-1.5 rounded-lg text-xs transition-all flex items-center gap-1.5"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            1. Regole Modelli
          </button>
          <button 
            @click="currentView = 'user'"
            :class="[currentView === 'user' ? 'bg-white text-indigo-700 shadow-sm font-bold' : 'text-slate-600 hover:text-slate-900 font-medium']"
            class="px-3.5 py-1.5 rounded-lg text-xs transition-all flex items-center gap-1.5"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            2. Competenze Utenti
          </button>
          <button 
            @click="currentView = 'summary'; loadSummary()"
            :class="[currentView === 'summary' ? 'bg-white text-indigo-700 shadow-sm font-bold' : 'text-slate-600 hover:text-slate-900 font-medium']"
            class="px-3.5 py-1.5 rounded-lg text-xs transition-all flex items-center gap-1.5"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            3. Resoconto Globale
          </button>
        </div>
      </div>
    </nav>

    <!-- NOTIFICA GLOBALE TOAST -->
    <div 
      v-if="toastMessage" 
      class="fixed bottom-5 right-5 z-50 max-w-md p-4 rounded-2xl shadow-xl border flex items-center justify-between gap-3 animate-in fade-in slide-in-from-bottom-5 duration-300"
      :class="[toastType === 'error' ? 'bg-rose-50 border-rose-200 text-rose-800' : 'bg-emerald-50 border-emerald-200 text-emerald-800']"
    >
      <div class="flex items-center gap-2.5 text-xs font-semibold">
        <svg v-if="toastType !== 'error'" class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        <svg v-else class="w-5 h-5 text-rose-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        <span>@{{ toastMessage }}</span>
      </div>
      <button @click="toastMessage = ''" class="text-slate-400 hover:text-slate-600 p-1">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
      </button>
    </div>

    <!-- ───────────────────────────────────────────────────────────── -->
    <!-- VISTA 1: ADMIN (Regole di Visibilità Modelli) -->
    <!-- ───────────────────────────────────────────────────────────── -->
    <main v-if="currentView === 'admin'" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 space-y-8">
      
      <!-- TESTATA -->
      <div class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-xs flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div class="flex items-center gap-3.5">
          <div class="p-3 bg-indigo-600 text-white rounded-2xl shadow-sm">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
          </div>
          <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Regole di Visibilità e Sicurezza Dati</h1>
            <p class="text-xs sm:text-sm text-slate-500 font-medium mt-0.5">Configura in modo visivo chi può vedere e modificare le informazioni in base alle proprie competenze.</p>
          </div>
        </div>
        <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 shadow-xs">
          <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
          @{{ definitions.length }} Regole Attive
        </span>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- FORM CONFIGURAZIONE GUIDATO -->
        <div class="lg:col-span-7 bg-white rounded-2xl border border-slate-200 shadow-xs p-6 sm:p-8 space-y-7">
          <div class="border-b border-slate-100 pb-4 flex items-center justify-between">
            <div>
              <h2 class="text-lg font-bold text-slate-900">Configurazione Nuova Regola</h2>
              <p class="text-xs text-slate-500 mt-0.5">Segui i 3 passi guidati: i colori ti mostrano esattamente come vengono collegati i dati.</p>
            </div>
            <span class="text-xs font-semibold px-2.5 py-1 bg-indigo-50 text-indigo-700 rounded-lg border border-indigo-100">
              Guida Visiva
            </span>
          </div>

          <form @submit.prevent="saveDefinition" class="space-y-7">
            
            <!-- PASSO 1: COSA VUOI PROTEGGERE -->
            <div 
              class="p-5 rounded-2xl border-2 transition-all space-y-3"
              :class="[focusedField === 'model_class' ? 'border-amber-400 bg-amber-50/40 ring-4 ring-amber-400/10' : 'border-slate-200 bg-slate-50/70']"
            >
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                  <span class="w-7 h-7 rounded-xl bg-amber-500 text-white font-black text-xs flex items-center justify-center shadow-sm">1</span>
                  <label class="text-xs font-extrabold text-slate-900 uppercase tracking-wider">
                    Quali schede o dati vuoi proteggere?
                  </label>
                </div>
                <span class="text-[11px] font-semibold text-amber-700 bg-amber-100 px-2 py-0.5 rounded-md">
                  Dato Principale
                </span>
              </div>
              
              <p class="text-xs text-slate-600 leading-relaxed">
                Scegli la tipologia di scheda su cui applicare il filtro (es. le <b>Anagrafiche</b>, le <b>Fatture</b>, i <b>Documenti</b> o i <b>Contratti</b>).
              </p>

              <select 
                v-model="form.model_class" 
                @focus="focusedField = 'model_class'"
                @blur="focusedField = null"
                @change="autoFillFields" 
                class="w-full border-2 border-slate-300 rounded-xl p-3 bg-white text-sm font-semibold text-slate-800 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 focus:outline-none shadow-sm transition"
                required
              >
                <option value="">-- Seleziona la Scheda da Proteggere --</option>
                <option v-for="m in availableModels" :key="m.class" :value="m.class">
                  @{{ m.name }} (@{{ m.class }})
                </option>
              </select>
            </div>

            <!-- PASSO 2: CRITERIO DI FILTRO -->
            <div 
              class="p-5 rounded-2xl border-2 transition-all space-y-3"
              :class="[focusedField === 'scope_filter' ? 'border-emerald-400 bg-emerald-50/40 ring-4 ring-emerald-400/10' : 'border-slate-200 bg-slate-50/70']"
            >
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                  <span class="w-7 h-7 rounded-xl bg-emerald-600 text-white font-black text-xs flex items-center justify-center shadow-sm">2</span>
                  <label class="text-xs font-extrabold text-slate-900 uppercase tracking-wider">
                    In base a cosa viene autorizzato l'operatore?
                  </label>
                </div>
                <span class="text-[11px] font-semibold text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded-md">
                  Criterio di Competenza
                </span>
              </div>
              
              <p class="text-xs text-slate-600 leading-relaxed">
                Scegli la caratteristica o l'appartenenza che decide chi può vedere la scheda (es. l'<b>Ufficio</b>, la <b>Sede</b>, o la <b>Qualifica</b>).
              </p>

              <select 
                v-model="form.scope_filter" 
                @focus="focusedField = 'scope_filter'"
                @blur="focusedField = null"
                @change="autoFillFields" 
                class="w-full border-2 border-slate-300 rounded-xl p-3 bg-white text-sm font-semibold text-slate-800 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none shadow-sm transition"
                required
              >
                <option value="">-- Seleziona il Criterio (es. Ufficio, Sede, Ruolo...) --</option>
                <option v-for="m in availableModels" :key="m.class" :value="m.class">
                  @{{ m.name }} (@{{ m.class }})
                </option>
              </select>
            </div>

            <!-- PASSO 3: SCHEMA VISIVO E COLLEGAMENTO DATI -->
            <div class="p-5 bg-slate-50/80 rounded-2xl border-2 border-slate-200 space-y-6">
              <div class="flex items-center justify-between border-b border-slate-200 pb-3">
                <div class="flex items-center gap-2.5">
                  <span class="w-7 h-7 rounded-xl bg-indigo-600 text-white font-black text-xs flex items-center justify-center shadow-sm">3</span>
                  <label class="text-xs font-extrabold text-slate-900 uppercase tracking-wider">
                    Dove si trova l'informazione nel database?
                  </label>
                </div>
                <span class="text-xs text-slate-500 font-medium">Struttura Collegamento</span>
              </div>

              <!-- SCELTA DEL TIPO -->
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div 
                  @click="setPivotMode(false)"
                  :class="[!form.has_pivot ? 'border-indigo-600 bg-indigo-50/70 ring-2 ring-indigo-500/20 text-indigo-950 shadow-sm' : 'border-slate-200 bg-white text-slate-700 hover:border-slate-300']"
                  class="p-4 border-2 rounded-2xl cursor-pointer transition-all flex flex-col justify-between space-y-2.5 select-none"
                >
                  <div class="flex items-center justify-between">
                    <span class="text-xs font-extrabold uppercase tracking-wide">Direttamente nella scheda</span>
                    <span v-if="!form.has_pivot" class="w-3 h-3 rounded-full bg-indigo-600 ring-2 ring-indigo-200"></span>
                  </div>
                  <p class="text-xs text-slate-600 leading-relaxed">
                    La scheda contiene già al suo interno il riferimento (es. <code>ufficio_id</code> direttamente nella tabella).
                  </p>
                  <div class="text-[10px] font-bold text-indigo-700 bg-indigo-100/70 px-2 py-1 rounded-lg self-start">
                    1 Valore per scheda (1:N)
                  </div>
                </div>

                <div 
                  @click="setPivotMode(true)"
                  :class="[form.has_pivot ? 'border-indigo-600 bg-indigo-50/70 ring-2 ring-indigo-500/20 text-indigo-950 shadow-sm' : 'border-slate-200 bg-white text-slate-700 hover:border-slate-300']"
                  class="p-4 border-2 rounded-2xl cursor-pointer transition-all flex flex-col justify-between space-y-2.5 select-none"
                >
                  <div class="flex items-center justify-between">
                    <span class="text-xs font-extrabold uppercase tracking-wide">In una tabella ponte separata</span>
                    <span v-if="form.has_pivot" class="w-3 h-3 rounded-full bg-indigo-600 ring-2 ring-indigo-200"></span>
                  </div>
                  <p class="text-xs text-slate-600 leading-relaxed">
                    Il collegamento avviene tramite una tabella pivot (es. una persona può avere <b>più qualifiche o ruoli</b>).
                  </p>
                  <div class="text-[10px] font-bold text-indigo-700 bg-indigo-100/70 px-2 py-1 rounded-lg self-start">
                    Valori multipli (Tabella Ponte / N:M)
                  </div>
                </div>
              </div>

              <!-- CAMPI TECNICI -->
              <div class="space-y-4 pt-2">
                <div v-if="form.has_pivot" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nome Tabella Ponte (Pivot)</label>
                    <input 
                      v-model="form.pivot_table" 
                      type="text" 
                      placeholder="es. anagrafica_qualifica" 
                      class="w-full border border-slate-300 rounded-xl p-2.5 text-xs font-mono font-medium focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600"
                      required
                    >
                  </div>
                  <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Chiave verso la Scheda Protetta</label>
                    <input 
                      v-model="form.pivot_foreign_key" 
                      type="text" 
                      placeholder="es. anagrafica_id" 
                      class="w-full border border-slate-300 rounded-xl p-2.5 text-xs font-mono font-medium focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600"
                      required
                    >
                  </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                      Nome Colonna del Criterio (@{{ form.has_pivot ? 'nella pivot' : 'nella scheda' }})
                    </label>
                    <input 
                      v-model="form.filter_key" 
                      type="text" 
                      placeholder="es. ufficio_id o qualifica_id" 
                      class="w-full border border-slate-300 rounded-xl p-2.5 text-xs font-mono font-medium focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600"
                      required
                    >
                  </div>
                  <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Colonna Gerarchia ad Albero (opzionale)</label>
                    <input 
                      v-model="form.parent_column" 
                      type="text" 
                      placeholder="es. padre_id o parent_id" 
                      class="w-full border border-slate-300 rounded-xl p-2.5 text-xs font-mono font-medium focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600"
                    >
                  </div>
                </div>
              </div>
            </div>

            <!-- PULSANTE DI SALVATAGGIO -->
            <button 
              type="submit" 
              :disabled="isSubmitting"
              class="w-full py-4 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-extrabold text-sm rounded-xl shadow-md shadow-indigo-600/20 transition-all flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50"
            >
              <svg v-if="!isSubmitting" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
              <svg v-else class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
              <span>@{{ isSubmitting ? 'Salvataggio in corso...' : 'Attiva Regola di Sicurezza' }}</span>
            </button>
          </form>
        </div>

        <!-- ANTEPRIMA QUERY SQL LIVE & REGOLE ESISTENTI -->
        <div class="lg:col-span-5 space-y-6">
          
          <!-- ANTEPRIMA SQL -->
          <div class="bg-slate-900 text-slate-100 rounded-2xl shadow-xl p-6 border border-slate-800 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
              <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-300">Simulatore Query SQL Live</h3>
              </div>
              <span class="text-[10px] font-mono text-emerald-400 bg-emerald-950/80 px-2 py-0.5 rounded border border-emerald-800">
                Live Preview
              </span>
            </div>

            <p class="text-xs text-slate-400 leading-relaxed">
              Ecco come Laravel applicherà il filtro automatico a livello di Database ogni volta che un operatore esegue query:
            </p>

            <div class="bg-slate-950 p-4 rounded-xl border border-slate-800 text-xs font-mono text-emerald-300 overflow-x-auto leading-relaxed">
              <code>@{{ simulatedSql }}</code>
            </div>
          </div>

          <!-- LISTA REGOLE ESISTENTI -->
          <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
              <h3 class="text-sm font-bold text-slate-900">Regole Configurate (@{{ definitions.length }})</h3>
              <button @click="loadDefinitions" class="text-xs text-indigo-600 hover:text-indigo-800 font-semibold">Aggiorna</button>
            </div>

            <div v-if="definitions.length === 0" class="text-center py-6 text-xs text-slate-400">
              Nessuna regola di visibilità ancora registrata.
            </div>

            <div v-else class="space-y-3 max-h-[420px] overflow-y-auto pr-1">
              <div 
                v-for="d in definitions" 
                :key="d.id"
                class="p-3.5 rounded-xl border border-slate-200 bg-slate-50/50 hover:bg-slate-50 transition flex items-center justify-between gap-3 text-xs"
              >
                <div class="space-y-1">
                  <div class="font-extrabold text-slate-800 flex items-center gap-1.5">
                    <span>@{{ formatClassName(d.model_class) }}</span>
                    <span class="text-slate-400">➜</span>
                    <span class="text-indigo-600">@{{ formatClassName(d.scope_filter) }}</span>
                  </div>
                  <div class="text-[11px] text-slate-500 font-mono">
                    chiave: @{{ d.filter_key }} <span v-if="d.pivot_table">| pivot: @{{ d.pivot_table }}</span>
                  </div>
                </div>

                <button 
                  @click="deleteDefinition(d.id)"
                  class="p-2 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition"
                  title="Elimina regola"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                </button>
              </div>
            </div>
          </div>

        </div>
      </div>
    </main>

    <!-- ───────────────────────────────────────────────────────────── -->
    <!-- VISTA 2: COMPETENZE UTENTI (Gestione Filtri Singolo Operatore) -->
    <!-- ───────────────────────────────────────────────────────────── -->
    <main v-if="currentView === 'user'" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 space-y-8">
      
      <!-- TESTATA -->
      <div class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-xs flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div class="flex items-center gap-3.5">
          <div class="p-3 bg-indigo-600 text-white rounded-2xl shadow-sm">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
          </div>
          <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Competenze e Filtri per Operatore</h1>
            <p class="text-xs sm:text-sm text-slate-500 font-medium mt-0.5">Assegna a ogni utente quali uffici, sedi o dati può visualizzare in base ai perimetri aziendali.</p>
          </div>
        </div>
      </div>

      <!-- SELETTORE UTENTE CON AUTOCOMPLETE -->
      <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4">
        <label class="block text-xs font-extrabold uppercase tracking-wider text-slate-700">
          Seleziona l'operatore da configurare
        </label>
        
        <div class="relative">
          <input 
            type="text" 
            v-model="userSearchQuery" 
            @input="onUserSearchInput"
            @focus="isUserDropdownOpen = true"
            placeholder="Cerca per nome, cognome o ID utente..."
            class="w-full border-2 border-slate-300 rounded-xl p-3.5 pl-10 text-sm font-semibold text-slate-800 focus:border-indigo-600 focus:ring-2 focus:ring-indigo-600/20 focus:outline-none shadow-xs transition"
          >
          <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
          </div>

          <!-- DROPDOWN RISULTATI -->
          <div 
            v-if="isUserDropdownOpen && userSearchResults.length > 0" 
            class="absolute z-30 mt-1 w-full bg-white border border-slate-200 rounded-xl shadow-xl max-h-60 overflow-y-auto py-1"
          >
            <div 
              v-for="u in userSearchResults" 
              :key="u.id"
              @click="selectUser(u)"
              class="px-4 py-2.5 hover:bg-indigo-50 cursor-pointer flex items-center justify-between border-b border-slate-100 last:border-0"
            >
              <div>
                <span class="text-xs font-bold text-slate-900">@{{ u.name || u.label || 'Utente #' + u.id }}</span>
                <span class="text-[11px] text-slate-500 ml-2">ID: @{{ u.id }}</span>
              </div>
              <span class="text-[10px] bg-slate-100 text-slate-600 px-2 py-0.5 rounded font-semibold">Seleziona</span>
            </div>
          </div>
        </div>

        <div v-if="selectedUser" class="p-4 bg-indigo-50/70 border border-indigo-200 rounded-xl flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-indigo-600 text-white font-black flex items-center justify-center text-sm shadow-xs">
              @{{ (selectedUser.name ? selectedUser.name.charAt(0) : 'U').toUpperCase() }}
            </div>
            <div>
              <div class="text-sm font-extrabold text-slate-900">@{{ selectedUser.name || 'Utente #' + selectedUser.id }}</div>
              <div class="text-xs text-slate-500 font-medium">ID Database: @{{ selectedUser.id }}</div>
            </div>
          </div>
          
          <button 
            @click="openCloneModal"
            class="px-4 py-2 bg-white hover:bg-slate-100 border border-slate-300 text-slate-800 text-xs font-bold rounded-xl shadow-xs transition flex items-center gap-1.5"
          >
            <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" /></svg>
            Clona Competenze
          </button>
        </div>
      </div>

      <!-- SEZIONE FILTRI UTENTE ATTIVI & AGGIUNTA NUOVO FILTRO -->
      <div v-if="selectedUser" class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- FORM AGGIUNTA FILTRO -->
        <div class="lg:col-span-5 bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-6">
          <div class="border-b border-slate-100 pb-3">
            <h3 class="text-sm font-bold text-slate-900">Assegna Nuova Competenza</h3>
            <p class="text-xs text-slate-500 mt-0.5">Definisci a quale elemento o ufficio ha accesso l'operatore.</p>
          </div>

          <form @submit.prevent="saveUserFilter" class="space-y-4">
            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">Criterio / Modello Competenza</label>
              <select 
                v-model="userForm.scope_filter" 
                class="w-full border border-slate-300 rounded-xl p-2.5 text-xs font-semibold text-slate-800 focus:border-indigo-600 focus:ring-2 focus:ring-indigo-600/20"
                required
              >
                <option value="">-- Seleziona Criterio (es. Ufficio, Sede) --</option>
                <option v-for="m in availableModels" :key="m.class" :value="m.class">
                  @{{ m.name }} (@{{ m.class }})
                </option>
              </select>
            </div>

            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">ID Valore Autorizzato</label>
              <input 
                v-model="userForm.filterable_id" 
                type="text" 
                placeholder="es. 5 (ID ufficio autorizzato)"
                class="w-full border border-slate-300 rounded-xl p-2.5 text-xs font-mono font-medium focus:border-indigo-600 focus:ring-2 focus:ring-indigo-600/20"
                required
              >
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Gruppo Logico (AND)</label>
                <input 
                  v-model.number="userForm.group" 
                  type="number" 
                  min="1"
                  class="w-full border border-slate-300 rounded-xl p-2.5 text-xs font-mono font-medium focus:border-indigo-600 focus:ring-2 focus:ring-indigo-600/20"
                  required
                >
                <p class="text-[10px] text-slate-400 mt-1">Stesso gruppo = OR. Gruppi diversi = AND.</p>
              </div>

              <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Includi Sotto-Nodi (Figli)</label>
                <div class="mt-2 flex items-center gap-2">
                  <input 
                    v-model="userForm.include_children" 
                    type="checkbox" 
                    id="chk_children"
                    class="w-4 h-4 text-indigo-600 rounded border-slate-300 focus:ring-indigo-500"
                  >
                  <label for="chk_children" class="text-xs font-semibold text-slate-700 cursor-pointer">Abilita Albero</label>
                </div>
              </div>
            </div>

            <div v-if="userForm.include_children">
              <label class="block text-xs font-bold text-slate-700 mb-1">Colonna Gerarchica (opzionale)</label>
              <input 
                v-model="userForm.parent_column" 
                type="text" 
                placeholder="es. padre_id (default automatico)"
                class="w-full border border-slate-300 rounded-xl p-2.5 text-xs font-mono font-medium focus:border-indigo-600"
              >
            </div>

            <button 
              type="submit" 
              class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold text-xs rounded-xl shadow-xs transition"
            >
              Assegna Competenza
            </button>
          </form>
        </div>

        <!-- LISTA FILTRI ATTIVI UTENTE -->
        <div class="lg:col-span-7 bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4">
          <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-sm font-bold text-slate-900">Competenze Assegnate (@{{ currentUserFilters.length }})</h3>
            <span class="text-xs text-slate-500">Operatore: @{{ selectedUser.name || '#' + selectedUser.id }}</span>
          </div>

          <div v-if="currentUserFilters.length === 0" class="text-center py-10 text-xs text-slate-400">
            Nessun filtro o vincolo assegnato a questo operatore (ha accesso globale non ristretto).
          </div>

          <div v-else class="space-y-3">
            <div 
              v-for="f in currentUserFilters" 
              :key="f.id"
              class="p-4 rounded-xl border border-slate-200 bg-slate-50 flex items-center justify-between gap-4"
            >
              <div class="space-y-1 text-xs">
                <div class="flex items-center gap-2">
                  <span class="font-extrabold text-slate-900">@{{ formatClassName(f.filterable_type) }}</span>
                  <span class="px-2 py-0.5 bg-indigo-100 text-indigo-800 rounded font-mono font-bold text-[11px]">ID: @{{ f.filterable_id }}</span>
                  <span class="px-2 py-0.5 bg-slate-200 text-slate-700 rounded text-[10px] font-bold">Gruppo @{{ f.group }}</span>
                </div>
                <div v-if="f.include_children" class="text-[11px] text-emerald-700 font-semibold flex items-center gap-1">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                  Include tutti i sotto-nodi gerarchici discendenti
                </div>
              </div>

              <button 
                @click="deleteUserFilter(f.id)"
                class="p-2 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition"
                title="Revoca competenza"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
              </button>
            </div>
          </div>
        </div>

      </div>
    </main>

    <!-- ───────────────────────────────────────────────────────────── -->
    <!-- VISTA 3: RESOCONTO GLOBALE PERMESSI (UserSummaryTable) -->
    <!-- ───────────────────────────────────────────────────────────── -->
    <main v-if="currentView === 'summary'" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 space-y-8">
      
      <!-- STAT CARDS -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs flex items-center gap-4">
          <div class="p-3 bg-indigo-50 text-indigo-600 rounded-2xl">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
          </div>
          <div>
            <div class="text-2xl font-black text-slate-900">@{{ summaryStats.total }}</div>
            <div class="text-xs text-slate-500 font-semibold">Operatori Totali</div>
          </div>
        </div>

        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs flex items-center gap-4">
          <div class="p-3 bg-emerald-50 text-emerald-600 rounded-2xl">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
          </div>
          <div>
            <div class="text-2xl font-black text-slate-900">@{{ summaryStats.withFilters }}</div>
            <div class="text-xs text-slate-500 font-semibold">Con Vincoli e Filtri Attivi</div>
          </div>
        </div>

        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs flex items-center gap-4">
          <div class="p-3 bg-amber-50 text-amber-600 rounded-2xl">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
          </div>
          <div>
            <div class="text-2xl font-black text-slate-900">@{{ summaryStats.withoutFilters }}</div>
            <div class="text-xs text-slate-500 font-semibold">Accesso Globale (Nessun Vincolo)</div>
          </div>
        </div>
      </div>

      <!-- TABELLA RESOCONTO CON RICERCA E FILTRI RAPIDI -->
      <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        
        <!-- HEADER TABELLA CON BARRA STRUMENTI -->
        <div class="p-6 border-b border-slate-200 flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4">
          <div class="flex items-center gap-2">
            <button 
              @click="summaryStatusFilter = 'all'"
              :class="[summaryStatusFilter === 'all' ? 'bg-indigo-600 text-white font-bold' : 'bg-slate-100 text-slate-600 font-medium hover:bg-slate-200']"
              class="px-3.5 py-1.5 rounded-xl text-xs transition"
            >
              Tutti (@{{ summaryStats.total }})
            </button>
            <button 
              @click="summaryStatusFilter = 'with_filters'"
              :class="[summaryStatusFilter === 'with_filters' ? 'bg-indigo-600 text-white font-bold' : 'bg-slate-100 text-slate-600 font-medium hover:bg-slate-200']"
              class="px-3.5 py-1.5 rounded-xl text-xs transition"
            >
              Con Permessi (@{{ summaryStats.withFilters }})
            </button>
            <button 
              @click="summaryStatusFilter = 'without_filters'"
              :class="[summaryStatusFilter === 'without_filters' ? 'bg-indigo-600 text-white font-bold' : 'bg-slate-100 text-slate-600 font-medium hover:bg-slate-200']"
              class="px-3.5 py-1.5 rounded-xl text-xs transition"
            >
              Senza Vincoli (@{{ summaryStats.withoutFilters }})
            </button>
          </div>

          <div class="relative w-full md:w-72">
            <input 
              v-model="summarySearchQuery" 
              type="text" 
              placeholder="Cerca per nome o ID..." 
              class="w-full border border-slate-300 rounded-xl p-2.5 pl-9 text-xs font-semibold focus:border-indigo-600 focus:outline-none"
            >
            <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
          </div>
        </div>

        <!-- CONTENUTO TABELLARE -->
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse text-xs">
            <thead>
              <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider">
                <th class="p-4 pl-6">Operatore</th>
                <th class="p-4">Stato Visibilità</th>
                <th class="p-4">Regole Assegnate</th>
                <th class="p-4 pr-6 text-right">Azioni Rapide</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-if="filteredSummaryList.length === 0">
                <td colspan="4" class="p-8 text-center text-slate-400">Nessun operatore corrispondente ai criteri cercati.</td>
              </tr>
              <tr 
                v-for="u in filteredSummaryList" 
                :key="u.id"
                class="hover:bg-slate-50/60 transition"
              >
                <td class="p-4 pl-6">
                  <div class="font-bold text-slate-900">@{{ u.name || 'Utente #' + u.id }}</div>
                  <div class="text-[11px] text-slate-400">ID: @{{ u.id }}</div>
                </td>
                <td class="p-4">
                  <span 
                    v-if="u.has_filters" 
                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200"
                  >
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    @{{ u.filters_count }} vincoli (@{{ u.groups_count }} gruppi)
                  </span>
                  <span 
                    v-else 
                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200"
                  >
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                    Nessun vincolo (Globale)
                  </span>
                </td>
                <td class="p-4">
                  <div v-if="u.summaryBadges && u.summaryBadges.length > 0" class="flex flex-wrap gap-1.5">
                    <span 
                      v-for="b in u.summaryBadges" 
                      :key="b.name"
                      class="px-2 py-0.5 rounded bg-indigo-50 text-indigo-700 border border-indigo-100 font-semibold text-[10px]"
                    >
                      @{{ b.count }}x @{{ b.name }}
                    </span>
                  </div>
                  <span v-else class="text-slate-400 text-[11px]">—</span>
                </td>
                <td class="p-4 pr-6 text-right space-x-2">
                  <button 
                    @click="goToConfigureUser(u)"
                    class="px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold rounded-xl transition"
                  >
                    Configura
                  </button>
                  <button 
                    v-if="u.has_filters"
                    @click="goToCloneUser(u)"
                    class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl transition"
                  >
                    Clona
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </main>

    <!-- MODALE CLONAZIONE COMPETENZE -->
    <div 
      v-if="isCloneModalOpen" 
      class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
    >
      <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl border border-slate-200 space-y-6 animate-in fade-in zoom-in-95 duration-200">
        <div class="flex items-center justify-between border-b border-slate-100 pb-4">
          <div class="flex items-center gap-2.5">
            <div class="p-2 bg-indigo-50 text-indigo-600 rounded-xl">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" /></svg>
            </div>
            <h3 class="text-base font-extrabold text-slate-900">Clona Competenze Operatore</h3>
          </div>
          <button @click="isCloneModalOpen = false" class="text-slate-400 hover:text-slate-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
          </button>
        </div>

        <p class="text-xs text-slate-600 leading-relaxed">
          Copia tutte le regole di <b>@{{ selectedUser?.name || 'Utente #' + selectedUser?.id }}</b> su uno o più operatori destinatari.
        </p>

        <!-- SELEZIONE DESTINATARI -->
        <div class="space-y-2">
          <label class="block text-xs font-bold text-slate-700">Seleziona Operatori Destinatari</label>
          <div class="max-h-48 overflow-y-auto border border-slate-200 rounded-xl p-2 divide-y divide-slate-100">
            <div 
              v-for="u in availableTargetUsers" 
              :key="u.id"
              class="py-2 px-2 flex items-center justify-between hover:bg-slate-50 rounded-lg"
            >
              <label :for="'target_u_' + u.id" class="text-xs font-semibold text-slate-800 cursor-pointer flex-1">
                @{{ u.name || 'Utente #' + u.id }} (ID: @{{ u.id }})
              </label>
              <input 
                :id="'target_u_' + u.id"
                type="checkbox" 
                :value="u.id" 
                v-model="cloneTargetUserIds"
                class="w-4 h-4 text-indigo-600 rounded border-slate-300 focus:ring-indigo-500 cursor-pointer"
              >
            </div>
          </div>
        </div>

        <!-- MODALITÀ (REPLACE / MERGE) -->
        <div class="grid grid-cols-2 gap-3">
          <div 
            @click="cloneMode = 'replace'"
            :class="[cloneMode === 'replace' ? 'border-indigo-600 bg-indigo-50/70 text-indigo-950 font-bold' : 'border-slate-200 text-slate-600']"
            class="p-3 border-2 rounded-xl cursor-pointer text-xs flex flex-col justify-between space-y-1 transition"
          >
            <span>Sovrascrivi (Replace)</span>
            <span class="text-[10px] text-slate-500 font-normal">Sostituisce i vecchi filtri</span>
          </div>

          <div 
            @click="cloneMode = 'merge'"
            :class="[cloneMode === 'merge' ? 'border-indigo-600 bg-indigo-50/70 text-indigo-950 font-bold' : 'border-slate-200 text-slate-600']"
            class="p-3 border-2 rounded-xl cursor-pointer text-xs flex flex-col justify-between space-y-1 transition"
          >
            <span>Unisci (Merge)</span>
            <span class="text-[10px] text-slate-500 font-normal">Aggiunge ai filtri esistenti</span>
          </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-2">
          <button 
            type="button" 
            @click="isCloneModalOpen = false" 
            class="px-4 py-2.5 text-xs font-bold text-slate-600 hover:text-slate-900 rounded-xl"
          >
            Annulla
          </button>
          <button 
            type="button" 
            @click="executeClone" 
            :disabled="cloneTargetUserIds.length === 0 || isCloning"
            class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-extrabold rounded-xl shadow-xs transition disabled:opacity-50"
          >
            @{{ isCloning ? 'Clonazione in corso...' : 'Conferma Clonazione' }}
          </button>
        </div>
      </div>
    </div>

  </div>

  <script>
    const { createApp, ref, reactive, computed, onMounted } = Vue;

    createApp({
      setup() {
        const apiPrefix = "{{ $apiPrefix }}";
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        const currentView = ref('admin');
        const focusedField = ref(null);
        const toastMessage = ref('');
        const toastType = ref('success');
        const isSubmitting = ref(false);
        const isCloning = ref(false);

        const showToast = (msg, type = 'success') => {
          toastMessage.value = msg;
          toastType.value = type;
          setTimeout(() => {
            if (toastMessage.value === msg) toastMessage.value = '';
          }, 4000);
        };

        const apiFetch = async (endpoint, options = {}) => {
          const headers = {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            ...(options.headers || {})
          };
          const response = await fetch(`${apiPrefix}${endpoint}`, { ...options, headers });
          if (!response.ok) {
            const err = await response.json().catch(() => ({ message: response.statusText }));
            throw new Error(err.message || 'Errore nella richiesta API');
          }
          return response.json();
        };

        // --- STATO ADMIN / DEFINIZIONI ---
        const availableModels = ref([]);
        const definitions = ref([]);

        const form = reactive({
          model_class: '',
          scope_filter: '',
          has_pivot: false,
          pivot_table: '',
          pivot_foreign_key: '',
          target_foreign_key: '',
          filter_key: '',
          parent_column: '',
          additional_where: null
        });

        const formatClassName = (fullClass) => {
          if (!fullClass) return '';
          return fullClass.split('\\').pop();
        };

        const setPivotMode = (isPivot) => {
          form.has_pivot = isPivot;
          autoFillFields();
        };

        const autoFillFields = () => {
          const modelName = formatClassName(form.model_class).toLowerCase();
          const scopeName = formatClassName(form.scope_filter).toLowerCase();

          if (scopeName) {
            form.filter_key = `${scopeName}_id`;
          }

          if (form.has_pivot && modelName && scopeName) {
            const segments = [modelName, scopeName].sort();
            form.pivot_table = `${segments[0]}_${segments[1]}`;
            form.pivot_foreign_key = `${modelName}_id`;
          } else if (!form.has_pivot) {
            form.pivot_table = '';
            form.pivot_foreign_key = '';
            form.target_foreign_key = '';
          }
        };

        const simulatedSql = computed(() => {
          const modelName = formatClassName(form.model_class).toLowerCase() || 'scheda';
          const table = modelName.endsWith('a') ? modelName.slice(0, -1) + 'e' : modelName + 's';
          const filterCol = form.filter_key || 'criterio_id';

          if (!form.has_pivot) {
            return `SELECT * FROM \`${table}\` WHERE \`${filterCol}\` IN (1, 2, 5);`;
          } else {
            const pivot = form.pivot_table || `${table}_criteri`;
            const pivotFk = form.pivot_foreign_key || `${modelName}_id`;
            return `SELECT * FROM \`${table}\` WHERE EXISTS (\n  SELECT 1 FROM \`${pivot}\`\n  WHERE \`${pivot}\`.\`${pivotFk}\` = \`${table}\`.\`id\`\n  AND \`${pivot}\`.\`${filterCol}\` IN (1, 2, 5)\n);`;
          }
        });

        const loadAvailableModels = async () => {
          try {
            const data = await apiFetch('/available-models');
            availableModels.value = data;
            if (data.length > 0 && !form.model_class) {
              form.model_class = data[0].class;
              if (data.length > 1) form.scope_filter = data[1].class;
              autoFillFields();
            }
          } catch (e) {
            console.error(e);
          }
        };

        const loadDefinitions = async () => {
          try {
            const data = await apiFetch('/filter-definitions');
            definitions.value = data;
          } catch (e) {
            console.error(e);
          }
        };

        const saveDefinition = async () => {
          isSubmitting.value = true;
          try {
            await apiFetch('/filter-definitions', {
              method: 'POST',
              body: JSON.stringify({
                model_class: form.model_class,
                scope_filter: form.scope_filter,
                pivot_table: form.has_pivot ? form.pivot_table : null,
                pivot_foreign_key: form.has_pivot ? form.pivot_foreign_key : null,
                target_foreign_key: form.has_pivot && form.target_foreign_key ? form.target_foreign_key : null,
                filter_key: form.filter_key,
                parent_column: form.parent_column ? form.parent_column : null,
                additional_where: form.additional_where
              })
            });
            showToast('Regola di visibilità salvata con successo!');
            await loadDefinitions();
          } catch (e) {
            showToast(e.message, 'error');
          } finally {
            isSubmitting.value = false;
          }
        };

        const deleteDefinition = async (id) => {
          if (!confirm('Vuoi davvero eliminare questa regola di visibilità?')) return;
          try {
            await apiFetch(`/filter-definitions/${id}`, { method: 'DELETE' });
            showToast('Regola eliminata con successo.');
            await loadDefinitions();
          } catch (e) {
            showToast(e.message, 'error');
          }
        };

        // --- STATO COMPETENZE UTENTE ---
        const userSearchQuery = ref('');
        const userSearchResults = ref([]);
        const isUserDropdownOpen = ref(false);
        const selectedUser = ref(null);
        const currentUserFilters = ref([]);
        let searchTimeout = null;

        const userForm = reactive({
          scope_filter: '',
          filterable_id: '',
          group: 1,
          include_children: false,
          parent_column: ''
        });

        const onUserSearchInput = () => {
          clearTimeout(searchTimeout);
          isUserDropdownOpen.value = true;
          searchTimeout = setTimeout(async () => {
            if (!userSearchQuery.value.trim()) {
              userSearchResults.value = [];
              return;
            }
            try {
              const res = await apiFetch(`/search-users?q=${encodeURIComponent(userSearchQuery.value)}`);
              userSearchResults.value = res.data || res;
            } catch (e) {
              console.error(e);
            }
          }, 300);
        };

        const selectUser = async (u) => {
          selectedUser.value = u;
          userSearchQuery.value = u.name || `Utente #${u.id}`;
          isUserDropdownOpen.value = false;
          await loadUserFilters(u.id);
        };

        const loadUserFilters = async (userId) => {
          try {
            const data = await apiFetch(`/user-filters?user_id=${userId}`);
            currentUserFilters.value = data;
          } catch (e) {
            console.error(e);
          }
        };

        const saveUserFilter = async () => {
          if (!selectedUser.value) return;
          try {
            await apiFetch('/user-filters', {
              method: 'POST',
              body: JSON.stringify({
                user_id: selectedUser.value.id,
                filterable_type: userForm.scope_filter,
                filterable_id: userForm.filterable_id,
                group: userForm.group || 1,
                include_children: userForm.include_children,
                parent_column: userForm.parent_column ? userForm.parent_column : null
              })
            });
            showToast('Competenza assegnata all\'operatore!');
            userForm.filterable_id = '';
            await loadUserFilters(selectedUser.value.id);
          } catch (e) {
            showToast(e.message, 'error');
          }
        };

        const deleteUserFilter = async (id) => {
          if (!confirm('Vuoi rimuovere questo filtro di competenza?')) return;
          try {
            await apiFetch(`/user-filters/${id}`, { method: 'DELETE' });
            showToast('Competenza revocata.');
            if (selectedUser.value) await loadUserFilters(selectedUser.value.id);
          } catch (e) {
            showToast(e.message, 'error');
          }
        };

        // --- STATO CLONAZIONE & RESOCONTO ---
        const isCloneModalOpen = ref(false);
        const cloneTargetUserIds = ref([]);
        const cloneMode = ref('replace');
        const summaryData = ref([]);
        const summaryStatusFilter = ref('all');
        const summarySearchQuery = ref('');

        const loadSummary = async () => {
          try {
            const data = await apiFetch('/user-filters-summary');
            summaryData.value = data;
          } catch (e) {
            console.error(e);
          }
        };

        const summaryStats = computed(() => {
          const total = summaryData.value.length;
          const withFilters = summaryData.value.filter(u => u.has_filters).length;
          const withoutFilters = total - withFilters;
          return { total, withFilters, withoutFilters };
        });

        const filteredSummaryList = computed(() => {
          let list = summaryData.value;
          if (summaryStatusFilter.value === 'with_filters') {
            list = list.filter(u => u.has_filters);
          } else if (summaryStatusFilter.value === 'without_filters') {
            list = list.filter(u => !u.has_filters);
          }
          if (summarySearchQuery.value.trim()) {
            const q = summarySearchQuery.value.toLowerCase();
            list = list.filter(u => 
              (u.name && u.name.toLowerCase().includes(q)) || 
              String(u.id).includes(q)
            );
          }
          return list;
        });

        const availableTargetUsers = computed(() => {
          if (!selectedUser.value) return summaryData.value;
          return summaryData.value.filter(u => u.id !== selectedUser.value.id);
        });

        const openCloneModal = async () => {
          if (summaryData.value.length === 0) await loadSummary();
          cloneTargetUserIds.value = [];
          cloneMode.value = 'replace';
          isCloneModalOpen.value = true;
        };

        const executeClone = async () => {
          if (!selectedUser.value || cloneTargetUserIds.value.length === 0) return;
          isCloning.value = true;
          try {
            await apiFetch('/user-filters/copy', {
              method: 'POST',
              body: JSON.stringify({
                source_user_id: selectedUser.value.id,
                target_user_ids: cloneTargetUserIds.value,
                mode: cloneMode.value
              })
            });
            showToast(`Competenze clonate con successo su ${cloneTargetUserIds.value.length} operatore/i!`);
            isCloneModalOpen.value = false;
            await loadSummary();
          } catch (e) {
            showToast(e.message, 'error');
          } finally {
            isCloning.value = false;
          }
        };

        const goToConfigureUser = async (u) => {
          await selectUser(u);
          currentView.value = 'user';
        };

        const goToCloneUser = async (u) => {
          await selectUser(u);
          currentView.value = 'user';
          openCloneModal();
        };

        onMounted(() => {
          loadAvailableModels();
          loadDefinitions();
          loadSummary();
        });

        return {
          currentView,
          focusedField,
          toastMessage,
          toastType,
          isSubmitting,
          isCloning,
          availableModels,
          definitions,
          form,
          simulatedSql,
          formatClassName,
          setPivotMode,
          autoFillFields,
          loadDefinitions,
          saveDefinition,
          deleteDefinition,
          userSearchQuery,
          userSearchResults,
          isUserDropdownOpen,
          selectedUser,
          currentUserFilters,
          userForm,
          onUserSearchInput,
          selectUser,
          saveUserFilter,
          deleteUserFilter,
          isCloneModalOpen,
          cloneTargetUserIds,
          cloneMode,
          availableTargetUsers,
          openCloneModal,
          executeClone,
          summaryData,
          summaryStatusFilter,
          summarySearchQuery,
          summaryStats,
          filteredSummaryList,
          loadSummary,
          goToConfigureUser,
          goToCloneUser
        };
      }
    }).mount('#app');
  </script>
</body>
</html>
