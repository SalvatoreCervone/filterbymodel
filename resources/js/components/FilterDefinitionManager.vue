<template>
  <div class="min-h-screen bg-slate-50/70 p-4 sm:p-6 lg:p-10 font-sans text-slate-800">
    <div class="max-w-7xl mx-auto space-y-8">
      
      <!-- HEADER PRINCIPALE -->
      <div class="bg-white rounded-2xl border border-slate-200/90 p-6 sm:p-8 shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
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
        <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 shadow-sm">
          <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
          {{ definitions.length }} Regole Attive
        </span>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- COLONNA SINISTRA: PROCEDURA GUIDATA INTUITIVA E A COLORI -->
        <div class="lg:col-span-7 bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8 space-y-7">
          
          <div class="border-b border-slate-100 pb-4 flex items-center justify-between">
            <div>
              <h2 class="text-lg font-bold text-slate-900">Configurazione Nuova Regola</h2>
              <p class="text-xs text-slate-500 mt-0.5">Segui i 3 passi guidati: i colori e lo schema ti mostrano esattamente come vengono collegati i dati.</p>
            </div>
            <span class="text-xs font-semibold px-2.5 py-1 bg-indigo-50 text-indigo-700 rounded-lg border border-indigo-100">
              Guida Visiva
            </span>
          </div>

          <form @submit.prevent="handleSubmit" class="space-y-7">
            
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
                  {{ m.name }} ({{ m.class }})
                </option>
              </select>

              <div class="flex justify-end pt-0.5">
                <button type="button" @click="toggleManualModel" class="text-[11px] text-slate-500 hover:text-slate-800 underline">
                  {{ manualModelMode ? 'Seleziona da elenco' : 'Scrivi nome classe a mano' }}
                </button>
              </div>
              <input 
                v-if="manualModelMode"
                type="text" 
                v-model="form.model_class" 
                @input="autoFillFields"
                placeholder="es. App\Models\Anagrafica" 
                class="w-full border border-slate-300 rounded-lg p-2.5 text-xs font-mono bg-white focus:ring-2 focus:ring-amber-500" 
              />
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
                  {{ m.name }} ({{ m.class }})
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

              <!-- SCELTA VISIVA SEMPLICE DEL TIPO DI COLLEGAMENTO -->
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
                    La scheda contiene già al suo interno il riferimento (es. ogni persona ha <b>1 solo ufficio</b> scritto nella sua scheda).
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
                    <span class="text-xs font-extrabold uppercase tracking-wide">In una tabella di collegamento separata</span>
                    <span v-if="form.has_pivot" class="w-3 h-3 rounded-full bg-indigo-600 ring-2 ring-indigo-200"></span>
                  </div>
                  <p class="text-xs text-slate-600 leading-relaxed">
                    Il collegamento avviene tramite un elenco separato (es. una persona può avere <b>più qualifiche o più uffici</b> contemporaneamente).
                  </p>
                  <div class="text-[10px] font-bold text-indigo-700 bg-indigo-100/70 px-2 py-1 rounded-lg self-start">
                    Valori multipli (Tabella Ponte / N:M)
                  </div>
                </div>

              </div>

              <!-- DIAGRAMMA GRAFICO DINAMICO CON ILLUMINAZIONE A COLORI CORRISPONDENTI -->
              <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs space-y-3">
                <div class="flex items-center justify-between text-xs text-slate-500 font-medium">
                  <span class="flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    Mappa visiva dei collegamenti (clicca o posizionati sui campi per illuminarli):
                  </span>
                </div>

                <!-- SCHEMA DIRETTO -->
                <div v-if="!form.has_pivot" class="flex flex-col sm:flex-row items-center gap-3">
                  <!-- BOX 1: SCHEDA -->
                  <div 
                    class="flex-1 w-full p-3 rounded-xl border-2 transition-all space-y-1"
                    :class="[focusedField === 'model_class' ? 'border-amber-400 bg-amber-50 shadow-md' : 'border-amber-200 bg-amber-50/40']"
                  >
                    <div class="text-[11px] font-bold text-amber-900 uppercase">Tabella Scheda Protetta</div>
                    <div class="text-xs font-extrabold text-amber-800">{{ targetTableName }}</div>
                  </div>

                  <div class="text-slate-400 font-bold text-lg hidden sm:block">➜</div>

                  <!-- BOX 2: COLONNA FILTRO -->
                  <div 
                    class="flex-1 w-full p-3 rounded-xl border-2 transition-all space-y-1"
                    :class="[focusedField === 'filter_key' ? 'border-emerald-400 bg-emerald-50 shadow-md ring-2 ring-emerald-400/20' : 'border-emerald-200 bg-emerald-50/40']"
                  >
                    <div class="text-[11px] font-bold text-emerald-900 uppercase">Colonna del Filtro</div>
                    <div class="text-xs font-mono font-bold text-emerald-800">{{ form.filter_key || 'seleziona...' }}</div>
                  </div>

                  <div class="text-slate-400 font-bold text-lg hidden sm:block">➜</div>

                  <!-- BOX 3: AUTORIZZAZIONI -->
                  <div class="flex-1 w-full p-3 rounded-xl border-2 border-indigo-200 bg-indigo-50/40 space-y-1">
                    <div class="text-[11px] font-bold text-indigo-900 uppercase">Permessi Utente</div>
                    <div class="text-xs font-bold text-indigo-800">ID Autorizzati [1, 2...]</div>
                  </div>
                </div>

                <!-- SCHEMA PIVOT (N:M) -->
                <div v-else class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                  <!-- BOX 1: SCHEDA PRINCIPALE -->
                  <div 
                    class="p-3 rounded-xl border-2 transition-all space-y-1"
                    :class="[focusedField === 'target_foreign_key' || focusedField === 'model_class' ? 'border-amber-400 bg-amber-50 shadow-md ring-2 ring-amber-400/20' : 'border-amber-200 bg-amber-50/40']"
                  >
                    <div class="text-[10px] font-bold text-amber-900 uppercase">1. Scheda Protetta</div>
                    <div class="text-xs font-bold text-amber-800">{{ targetTableName }}</div>
                    <div class="text-[10px] text-amber-700 font-mono bg-white px-1.5 py-0.5 rounded border border-amber-200">
                      Campo: {{ form.target_foreign_key || 'id (Predefinito)' }}
                    </div>
                  </div>

                  <!-- BOX 2: TABELLA PONTE -->
                  <div 
                    class="p-3 rounded-xl border-2 transition-all space-y-1.5"
                    :class="[focusedField === 'pivot_table' || focusedField === 'pivot_foreign_key' || focusedField === 'filter_key' ? 'border-sky-400 bg-sky-50 shadow-md ring-2 ring-sky-400/20' : 'border-sky-200 bg-sky-50/40']"
                  >
                    <div class="text-[10px] font-bold text-sky-900 uppercase">2. Tabella di Collegamento</div>
                    <div class="text-xs font-bold text-sky-800">{{ form.pivot_table || 'tabella_collegamento' }}</div>
                    <div class="text-[10px] text-sky-800 font-mono bg-white p-1 rounded border border-sky-200 space-y-0.5">
                      <div :class="[focusedField === 'pivot_foreign_key' ? 'text-amber-700 font-bold' : '']">🔗 Verso Scheda: {{ form.pivot_foreign_key || '...' }}</div>
                      <div :class="[focusedField === 'filter_key' ? 'text-emerald-700 font-bold' : '']">🎯 Verso Filtro: {{ form.filter_key || '...' }}</div>
                    </div>
                  </div>

                  <!-- BOX 3: PERMESSI UTENTE -->
                  <div 
                    class="p-3 rounded-xl border-2 transition-all space-y-1"
                    :class="[focusedField === 'scope_filter' ? 'border-emerald-400 bg-emerald-50 shadow-md' : 'border-emerald-200 bg-emerald-50/40']"
                  >
                    <div class="text-[10px] font-bold text-emerald-900 uppercase">3. Criterio Autorizzato</div>
                    <div class="text-xs font-bold text-emerald-800">{{ formatClassName(form.scope_filter) || 'Criterio' }}</div>
                    <div class="text-[10px] text-emerald-700 font-mono bg-white px-1.5 py-0.5 rounded border border-emerald-200">
                      ID Operatore [1, 4, 12...]
                    </div>
                  </div>
                </div>

              </div>

              <!-- CAMPI RELAZIONE DIRETTA -->
              <div v-if="!form.has_pivot" class="space-y-2 pt-2">
                <div class="flex items-center justify-between">
                  <label class="block text-xs font-extrabold text-slate-800">
                    Nome della colonna di riferimento presente nella scheda
                  </label>
                  <span class="text-[11px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-md">
                    Colonna del Filtro
                  </span>
                </div>
                <p class="text-xs text-slate-500">
                  Scrivi il nome esatto della colonna che contiene l'ufficio o la categoria dentro la tabella della scheda (es. <code>ufficio_id</code> o <code>sede_id</code>).
                </p>
                <input 
                  type="text" 
                  v-model="form.filter_key" 
                  @focus="focusedField = 'filter_key'"
                  @blur="focusedField = null"
                  class="w-full border-2 border-slate-300 rounded-xl p-3 text-xs font-mono font-bold text-slate-900 bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 shadow-sm" 
                  placeholder="es. ufficio_id" 
                  required 
                />
              </div>

              <!-- CAMPI TABELLA DI COLLEGAMENTO SEPARATA (PIVOT) -->
              <div v-else class="space-y-5 pt-2">
                
                <!-- 1. Nome Tabella di Collegamento -->
                <div 
                  class="p-4 rounded-xl border-2 transition-all space-y-2"
                  :class="[focusedField === 'pivot_table' ? 'border-sky-400 bg-sky-50/40' : 'border-slate-200 bg-white']"
                >
                  <div class="flex items-center justify-between">
                    <label class="block text-xs font-extrabold text-slate-900">
                      A. Nome della Tabella di Collegamento
                    </label>
                    <span class="text-[11px] font-bold text-sky-700 bg-sky-50 border border-sky-200 px-2 py-0.5 rounded-md">
                      Tabella Ponte
                    </span>
                  </div>
                  <p class="text-xs text-slate-500">
                    Il nome della tabella che memorizza le associazioni multiple (es. <code>anagrafica_ufficio</code> o <code>utente_ruolo</code>).
                  </p>
                  <input 
                    type="text" 
                    v-model="form.pivot_table" 
                    @focus="focusedField = 'pivot_table'"
                    @blur="focusedField = null"
                    class="w-full border-2 border-slate-300 rounded-xl p-2.5 text-xs font-mono font-bold text-slate-900 bg-white focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 shadow-sm" 
                    placeholder="es. anagrafica_ufficio" 
                    required 
                  />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  
                  <!-- 2. Colonna di collegamento alla scheda -->
                  <div 
                    class="p-4 rounded-xl border-2 transition-all space-y-2"
                    :class="[focusedField === 'pivot_foreign_key' ? 'border-amber-400 bg-amber-50/40' : 'border-slate-200 bg-white']"
                  >
                    <div class="flex items-center justify-between">
                      <label class="block text-xs font-extrabold text-slate-900">
                        B. Colonna che punta alla Scheda
                      </label>
                      <span class="text-[10px] font-bold text-amber-700 bg-amber-50 border border-amber-200 px-1.5 py-0.5 rounded">
                        Rif. Scheda
                      </span>
                    </div>
                    <p class="text-[11px] text-slate-500">
                      Il campo nella tabella ponte che identifica la scheda (es. <code>anagrafica_id</code>).
                    </p>
                    <input 
                      type="text" 
                      v-model="form.pivot_foreign_key" 
                      @focus="focusedField = 'pivot_foreign_key'"
                      @blur="focusedField = null"
                      class="w-full border-2 border-slate-300 rounded-xl p-2.5 text-xs font-mono font-bold text-slate-900 bg-white focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 shadow-sm" 
                      placeholder="es. anagrafica_id" 
                      required 
                    />
                  </div>

                  <!-- 3. Colonna del filtro autorizzato -->
                  <div 
                    class="p-4 rounded-xl border-2 transition-all space-y-2"
                    :class="[focusedField === 'filter_key' ? 'border-emerald-400 bg-emerald-50/40' : 'border-slate-200 bg-white']"
                  >
                    <div class="flex items-center justify-between">
                      <label class="block text-xs font-extrabold text-slate-900">
                        C. Colonna che contiene il Filtro
                      </label>
                      <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-1.5 py-0.5 rounded">
                        Rif. Filtro
                      </span>
                    </div>
                    <p class="text-[11px] text-slate-500">
                      Il campo nella tabella ponte che contiene l'ufficio/qualifica (es. <code>ufficio_id</code>).
                    </p>
                    <input 
                      type="text" 
                      v-model="form.filter_key" 
                      @focus="focusedField = 'filter_key'"
                      @blur="focusedField = null"
                      class="w-full border-2 border-slate-300 rounded-xl p-2.5 text-xs font-mono font-bold text-slate-900 bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 shadow-sm" 
                      placeholder="es. ufficio_id" 
                      required 
                    />
                  </div>

                </div>

                <!-- 4. Identificativo personalizzato (opzionale) -->
                <div 
                  class="p-4 rounded-xl border-2 transition-all space-y-2"
                  :class="[focusedField === 'target_foreign_key' ? 'border-amber-400 bg-amber-50/40' : 'border-slate-200 bg-white']"
                >
                  <div class="flex items-center justify-between">
                    <label class="block text-xs font-extrabold text-slate-900">
                      D. Identificativo Personalizzato della Scheda (Opzionale)
                    </label>
                    <span class="text-[10px] font-bold text-slate-600 bg-slate-100 px-2 py-0.5 rounded">
                      Default: 'id'
                    </span>
                  </div>
                  <p class="text-xs text-slate-500 leading-relaxed">
                    Lascia vuoto questo campo se la scheda usa il normale numero identificativo progressivo (<code>id</code>). Compilalo solo se il collegamento usa un codice speciale (es. <code>codice_fiscale</code>).
                  </p>
                  <input 
                    type="text" 
                    v-model="form.target_foreign_key" 
                    @focus="focusedField = 'target_foreign_key'"
                    @blur="focusedField = null"
                    class="w-full border-2 border-slate-300 rounded-xl p-2 text-xs font-mono bg-slate-50 focus:bg-white focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20" 
                    placeholder="Lascia vuoto per usare il normale 'id'" 
                  />
                </div>

              </div>

              <!-- COLONNA GERARCHIA AD ALBERO -->
              <div class="space-y-2 pt-3 border-t border-slate-200">
                <div class="flex items-center justify-between">
                  <label class="block text-xs font-extrabold text-slate-800">
                    Colonna per la Gerarchia ad Albero (Opzionale)
                  </label>
                  <span class="text-[11px] font-semibold text-indigo-700 bg-indigo-50 border border-indigo-200 px-2 py-0.5 rounded-md">
                    Risoluzione Figli
                  </span>
                </div>
                <p class="text-xs text-slate-500">
                  Se questo criterio ha sotto-elementi (figli) e la colonna del genitore non si chiama <code>padre_id</code>, scrivi qui il suo nome (es. <code>parent_id</code>, <code>id_padre</code>).
                </p>
                <input 
                  type="text" 
                  v-model="form.parent_column" 
                  class="w-full border-2 border-slate-300 rounded-xl p-2.5 text-xs font-mono bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 shadow-sm" 
                  placeholder="Lascia vuoto se usa 'padre_id' (Default)" 
                />
              </div>

              <!-- FILTRO CONDIZIONALE EXTRA -->
              <div class="space-y-2.5 pt-3 border-t border-slate-200">
                <div class="flex items-center justify-between">
                  <label class="block text-xs font-extrabold text-slate-800">
                    Condizione Aggiuntiva Fissa (Opzionale)
                  </label>
                  <span class="text-[11px] font-semibold text-slate-500 bg-slate-100 px-2 py-0.5 rounded">
                    Filtro Extra
                  </span>
                </div>
                <p class="text-xs text-slate-500">
                  Vuoi mostrare solo i record con un determinato stato fisso? (es. solo quelli attivi).
                </p>

                <div class="flex flex-wrap gap-2">
                  <button type="button" @click="setJsonPreset('stato', 'attivo')" class="text-xs px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium rounded-lg transition">
                    + Solo Attivi: {"stato": "attivo"}
                  </button>
                  <button type="button" @click="setJsonPreset('visibile', 1)" class="text-xs px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium rounded-lg transition">
                    + Solo Visibili: {"visibile": 1}
                  </button>
                </div>

                <textarea 
                  v-model="rawAdditionalWhere" 
                  rows="2" 
                  class="w-full border-2 border-slate-300 rounded-xl p-2.5 text-xs font-mono bg-white focus:ring-2 focus:ring-indigo-500 shadow-sm" 
                  placeholder='es. {"stato": "attivo"}' 
                  @input="validateJson"
                ></textarea>
                <p v-if="jsonError" class="text-xs text-rose-600 font-medium">⚠️ Formato non valido. Esempio corretto: {"stato": "attivo"}</p>
              </div>

            </div>

            <!-- ANTEPRIMA IN ITALIANO SEMPLICE -->
            <div v-if="form.model_class && form.scope_filter" class="p-4 bg-indigo-50/70 rounded-2xl border border-indigo-100 text-indigo-950 space-y-1.5">
              <div class="flex items-center gap-2 text-xs font-extrabold uppercase tracking-wider text-indigo-800">
                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Come funzionerà per gli utenti:
              </div>
              <p class="text-xs leading-relaxed text-slate-700">
                Ogni volta che un operatore cerca o visualizza le schede <strong class="text-amber-900 font-bold bg-amber-100 px-1.5 py-0.5 rounded">{{ formatClassName(form.model_class) }}</strong>, 
                il sistema mostrerà soltanto i record associati al suo <strong class="text-emerald-900 font-bold bg-emerald-100 px-1.5 py-0.5 rounded">{{ formatClassName(form.scope_filter) }}</strong> di competenza.
              </p>
            </div>

            <!-- PULSANTE SALVATAGGIO -->
            <button 
              type="submit" 
              class="w-full bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white font-bold rounded-2xl p-4 text-sm shadow-md hover:shadow-lg transition-all duration-200 flex items-center justify-center gap-2"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
              <span>Attiva Regola di Sicurezza</span>
            </button>

          </form>
        </div>

        <!-- COLONNA DESTRA: ELENCO REGOLE ATTIVE -->
        <div class="lg:col-span-5 space-y-6">
          <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-100 bg-slate-50/80 flex items-center justify-between">
              <div>
                <h3 class="font-bold text-slate-900 text-sm">Regole Attualmente Operative</h3>
                <p class="text-xs text-slate-400">Regole attive per la protezione dei dati</p>
              </div>
              <span class="text-xs bg-indigo-100 text-indigo-700 font-bold px-2.5 py-1 rounded-lg">
                {{ definitions.length }}
              </span>
            </div>

            <div v-if="definitions.length === 0" class="p-10 text-center text-slate-400 text-sm">
              Nessuna regola definita.
            </div>

            <div v-else class="divide-y divide-slate-100 max-h-[600px] overflow-y-auto">
              <div v-for="def in definitions" :key="def.id" class="p-4 hover:bg-slate-50 transition space-y-2.5">
                <div class="flex items-start justify-between gap-2">
                  <div class="space-y-1">
                    <div class="text-sm font-bold text-slate-900 flex items-center gap-2 flex-wrap">
                      <span class="bg-amber-50 text-amber-900 border border-amber-200 px-2 py-0.5 rounded-md font-extrabold">{{ formatClassName(def.model_class) }}</span>
                      <span v-if="def.pivot_table" class="px-2 py-0.5 text-[10px] font-semibold bg-sky-50 text-sky-700 border border-sky-200 rounded-md">
                        Collegamento Esterno
                      </span>
                      <span v-else class="px-2 py-0.5 text-[10px] font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200 rounded-md">
                        Collegamento Diretto
                      </span>
                    </div>
                    <p class="text-xs text-slate-600">
                      Filtrato per: <strong class="text-emerald-700 bg-emerald-50 border border-emerald-200 px-1.5 py-0.5 rounded font-bold">{{ formatClassName(def.scope_filter) }}</strong>
                    </p>
                  </div>
                  <button @click="deleteDefinition(def.id)" class="text-xs text-rose-600 hover:text-rose-700 font-medium bg-rose-50 hover:bg-rose-100 px-2.5 py-1 rounded-lg transition">
                    Rimuovi
                  </button>
                </div>

                <div class="text-[11px] text-slate-600 bg-slate-50 p-2.5 rounded-xl border border-slate-100 space-y-1 font-sans">
                  <div><strong>Scheda protetta:</strong> <span class="font-mono">{{ def.model_class }}</span></div>
                  <div><strong>Criterio:</strong> <span class="font-mono">{{ def.scope_filter }}</span></div>
                  <div><strong>Campo filtro:</strong> <span class="font-mono font-bold text-emerald-800">{{ def.filter_key }}</span></div>
                  <div v-if="def.parent_column"><strong>Colonna albero:</strong> <span class="font-mono text-indigo-700 font-bold">{{ def.parent_column }}</span></div>
                  <div v-if="def.pivot_table"><strong>Tabella ponte:</strong> <span class="font-mono text-sky-800">{{ def.pivot_table }}</span> (rif. scheda: <span class="font-mono text-amber-800">{{ def.pivot_foreign_key }}</span>)</div>
                  <div v-if="def.target_foreign_key"><strong>Identificativo speciale:</strong> <span class="font-mono">{{ def.target_foreign_key }}</span></div>
                  <div v-if="def.additional_where"><strong>Condizione fissa:</strong> <span class="font-mono">{{ typeof def.additional_where === 'string' ? def.additional_where : JSON.stringify(def.additional_where) }}</span></div>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>

      <!-- ANTEPRIMA QUERY SQL & ELOQUENT IN BASSO -->
      <div class="bg-slate-900 text-slate-100 rounded-2xl border border-slate-800 shadow-xl overflow-hidden">
        <div class="p-5 sm:p-6 border-b border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-950/60">
          <div class="space-y-1">
            <div class="flex items-center gap-2.5">
              <span class="p-1.5 bg-indigo-500/20 text-indigo-400 rounded-lg border border-indigo-500/30">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
              </span>
              <h3 class="font-bold text-base text-white tracking-tight">Anteprima della Query Applicata Automaticamente</h3>
            </div>
            <p class="text-xs text-slate-400">
              Ecco come il sistema traduce visivamente questa regola nella query finale al database.
            </p>
          </div>

          <div class="flex items-center gap-1.5 bg-slate-800/90 p-1 rounded-xl border border-slate-700/60">
            <button 
              type="button" 
              @click="activeQueryTab = 'select'"
              :class="[activeQueryTab === 'select' ? 'bg-indigo-600 text-white font-semibold' : 'text-slate-400 hover:text-white']"
              class="px-3 py-1.5 rounded-lg text-xs transition"
            >
              1. Query di Ricerca (SELECT)
            </button>
            <button 
              type="button" 
              @click="activeQueryTab = 'write'"
              :class="[activeQueryTab === 'write' ? 'bg-indigo-600 text-white font-semibold' : 'text-slate-400 hover:text-white']"
              class="px-3 py-1.5 rounded-lg text-xs transition"
            >
              2. Controllo Scrittura / Delete
            </button>
            <button 
              type="button" 
              @click="activeQueryTab = 'eloquent'"
              :class="[activeQueryTab === 'eloquent' ? 'bg-indigo-600 text-white font-semibold' : 'text-slate-400 hover:text-white']"
              class="px-3 py-1.5 rounded-lg text-xs transition"
            >
              3. Codice PHP Eloquent
            </button>
          </div>
        </div>

        <div class="p-6 space-y-4">
          <!-- SELECT QUERY -->
          <div v-if="activeQueryTab === 'select'" class="space-y-3">
            <div class="flex items-center justify-between text-xs text-slate-400">
              <span>Query eseguita automaticamente ogni volta che l'utente cerca dati sul modello <code>{{ sampleModelName }}</code></span>
              <span class="text-[11px] font-mono text-emerald-400 bg-emerald-950/60 px-2 py-0.5 rounded border border-emerald-800">GlobalScope Attivo</span>
            </div>

            <div class="bg-black/60 rounded-xl p-4 font-mono text-xs text-indigo-300 border border-slate-800 overflow-x-auto leading-relaxed whitespace-pre-wrap">
<span class="text-pink-400 font-bold">SELECT</span> * <span class="text-pink-400 font-bold">FROM</span> <span class="text-amber-300 font-semibold" :class="[focusedField === 'model_class' ? 'bg-amber-400/30 px-1 rounded' : '']">{{ targetTableName }}</span>
<span class="text-pink-400 font-bold">WHERE</span> <span class="text-slate-400">(</span>
<span v-if="!form.has_pivot">  <span class="text-amber-300">{{ targetTableName }}</span>.<span class="text-emerald-400 font-bold" :class="[focusedField === 'filter_key' ? 'bg-emerald-400/30 px-1 rounded' : '']">{{ form.filter_key || 'colonna_filtro' }}</span> <span class="text-pink-400 font-bold">IN</span> (<span class="text-emerald-300 font-semibold">:elenco_id_autorizzati_utente</span>)<span v-if="hasAdditionalWhereClause"><br>  <span class="text-pink-400 font-bold">AND</span> <span class="text-sky-300">{{ additionalWhereSql }}</span></span></span>
<span v-else>  <span class="text-amber-300">{{ targetTableName }}</span>.<span class="text-amber-300 font-bold" :class="[focusedField === 'target_foreign_key' ? 'bg-amber-400/30 px-1 rounded' : '']">{{ form.target_foreign_key || 'id' }}</span> <span class="text-pink-400 font-bold">IN</span> (
    <span class="text-pink-400 font-bold">SELECT</span> <span class="text-amber-300 font-semibold" :class="[focusedField === 'pivot_foreign_key' ? 'bg-amber-400/30 px-1 rounded' : '']">{{ form.pivot_foreign_key || 'colonna_scheda' }}</span> 
    <span class="text-pink-400 font-bold">FROM</span> <span class="text-sky-300 font-bold" :class="[focusedField === 'pivot_table' ? 'bg-sky-400/30 px-1 rounded' : '']">{{ form.pivot_table || 'tabella_ponte' }}</span> 
    <span class="text-pink-400 font-bold">WHERE</span> <span class="text-sky-300">{{ form.pivot_table || 'tabella_ponte' }}</span>.<span class="text-emerald-400 font-bold" :class="[focusedField === 'filter_key' ? 'bg-emerald-400/30 px-1 rounded' : '']">{{ form.filter_key || 'colonna_filtro' }}</span> <span class="text-pink-400 font-bold">IN</span> (<span class="text-emerald-300 font-semibold">:elenco_id_autorizzati_utente</span>)<span v-if="hasAdditionalWhereClause"><br>    <span class="text-pink-400 font-bold">AND</span> <span class="text-sky-300">{{ additionalWhereSql }}</span></span>
  )</span>
<span class="text-slate-400">)</span>
            </div>

            <p class="text-xs text-slate-400 leading-relaxed">
              💡 <b>Spiegazione:</b> Il parametro <code class="text-emerald-400">:elenco_id_autorizzati_utente</code> contiene la lista dei codici (es. <code>[1, 4, 8]</code>) assegnati all'operatore per <b>{{ formatClassName(form.scope_filter) || 'questo criterio' }}</b>.
            </p>
          </div>

          <!-- WRITE / DELETE VALIDATION -->
          <div v-else-if="activeQueryTab === 'write'" class="space-y-3">
            <div class="flex items-center justify-between text-xs text-slate-400">
              <span>Controllo eseguito prima di salvare o eliminare un record per bloccare modifiche non autorizzate</span>
              <span class="text-[11px] font-mono text-amber-400 bg-amber-950/60 px-2 py-0.5 rounded border border-amber-800">Protezione Scrittura</span>
            </div>

            <div class="bg-black/60 rounded-xl p-4 font-mono text-xs text-amber-200 border border-slate-800 overflow-x-auto leading-relaxed whitespace-pre-wrap">
<span v-if="!form.has_pivot">// Verifica se il valore della scheda appartiene alle competenze dell'utente:
if (!in_array($record-><span class="text-emerald-300 font-bold">{{ form.filter_key || 'colonna_filtro' }}</span>, $idAutorizzatiUtente)) {
    throw ValidationException::withMessages(['authorization' => 'Non puoi modificare questo record.']);
}</span>
<span v-else>// Verifica esistenza dell'associazione nella tabella ponte:
$autorizzato = DB::table('<span class="text-sky-300 font-bold">{{ form.pivot_table || 'tabella_ponte' }}</span>')
    ->where('<span class="text-amber-300 font-bold">{{ form.pivot_foreign_key || 'colonna_scheda' }}</span>', $record->{{ form.target_foreign_key ? form.target_foreign_key : 'getKey()' }})
    ->whereIn('<span class="text-emerald-300 font-bold">{{ form.filter_key || 'colonna_filtro' }}</span>', $idAutorizzatiUtente)
    ->exists();

if (!$autorizzato) {
    throw ValidationException::withMessages(['authorization' => 'Non puoi modificare questo record.']);
}</span>
            </div>
          </div>

          <!-- ELOQUENT BUILDER -->
          <div v-else class="space-y-3">
            <div class="flex items-center justify-between text-xs text-slate-400">
              <span>Costruzione dinamica della clausola nel Builder Eloquent</span>
              <span class="text-[11px] font-mono text-purple-400 bg-purple-950/60 px-2 py-0.5 rounded border border-purple-800">PHP / Laravel</span>
            </div>

            <div class="bg-black/60 rounded-xl p-4 font-mono text-xs text-purple-200 border border-slate-800 overflow-x-auto leading-relaxed whitespace-pre-wrap">
$builder->where(function (Builder $subQuery) {
<span v-if="!form.has_pivot">    $subQuery->whereIn('<span class="text-amber-300">{{ targetTableName }}</span>.<span class="text-emerald-300 font-bold">{{ form.filter_key || 'colonna_filtro' }}</span>', $allowedIds);</span>
<span v-else>    $subQuery->whereIn('<span class="text-amber-300">{{ targetTableName }}</span>.<span class="text-amber-300 font-bold">{{ form.target_foreign_key || 'id' }}</span>', function ($query) use ($data) {
        $query->select('<span class="text-amber-300 font-bold">{{ form.pivot_foreign_key || 'colonna_scheda' }}</span>')
              ->from('<span class="text-sky-300 font-bold">{{ form.pivot_table || 'tabella_ponte' }}</span>')
              ->whereIn('<span class="text-emerald-300 font-bold">{{ form.filter_key || 'colonna_filtro' }}</span>', $data['ids']);
    });</span>
});
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { filterService } from '../services/filterService';

const definitions = ref([]);
const availableModels = ref([]);
const rawAdditionalWhere = ref('');
const jsonError = ref(false);
const manualModelMode = ref(false);
const activeQueryTab = ref('select');
const focusedField = ref(null);

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

const sampleModelName = computed(() => {
  return formatClassName(form.model_class) || 'Scheda';
});

const targetTableName = computed(() => {
  const name = formatClassName(form.model_class).toLowerCase();
  if (!name) return 'schede';
  if (name.endsWith('a')) return name.slice(0, -1) + 'e';
  if (name.endsWith('o')) return name.slice(0, -1) + 'i';
  if (name.endsWith('e')) return name.slice(0, -1) + 'i';
  return name + 's';
});

const hasAdditionalWhereClause = computed(() => {
  return form.additional_where && Object.keys(form.additional_where).length > 0;
});

const additionalWhereSql = computed(() => {
  if (!hasAdditionalWhereClause.value) return '';
  const clauses = [];
  for (const [key, value] of Object.entries(form.additional_where)) {
    const formattedVal = typeof value === 'string' ? `'${value}'` : value;
    clauses.push(`${key} = ${formattedVal}`);
  }
  return clauses.join(' AND ');
});

const toggleManualModel = () => {
  manualModelMode.value = !manualModelMode.value;
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

const setJsonPreset = (key, val) => {
  let current = {};
  try {
    if (rawAdditionalWhere.value) {
      current = JSON.parse(rawAdditionalWhere.value);
    }
  } catch (e) {
    current = {};
  }
  current[key] = val;
  rawAdditionalWhere.value = JSON.stringify(current);
  validateJson();
};

const validateJson = () => {
  if (!rawAdditionalWhere.value || !rawAdditionalWhere.value.trim()) {
    form.additional_where = null;
    jsonError.value = false;
    return;
  }
  try {
    const parsed = JSON.parse(rawAdditionalWhere.value);
    form.additional_where = parsed;
    jsonError.value = false;
  } catch (e) {
    jsonError.value = true;
  }
};

const fetchData = async () => {
  try {
    const [models, defs] = await Promise.all([
      filterService.getAvailableModels(),
      filterService.getFilterDefinitions()
    ]);
    availableModels.value = models || [];
    definitions.value = defs || [];
  } catch (err) {
    console.error("Errore durante il caricamento dei dati di configurazione:", err);
  }
};

const handleSubmit = async () => {
  validateJson();
  if (jsonError.value) return;

  try {
    const payload = {
      model_class: form.model_class,
      scope_filter: form.scope_filter,
      has_pivot: form.has_pivot,
      pivot_table: form.has_pivot ? form.pivot_table : null,
      pivot_foreign_key: form.has_pivot ? form.pivot_foreign_key : null,
      target_foreign_key: form.has_pivot && form.target_foreign_key ? form.target_foreign_key : null,
      filter_key: form.filter_key,
      parent_column: form.parent_column ? form.parent_column : null,
      additional_where: form.additional_where
    };

    const savedData = await filterService.saveFilterDefinition(payload);
    definitions.value.push(savedData);
    
    // Reset dello stato del form
    form.model_class = '';
    form.scope_filter = '';
    form.has_pivot = false;
    form.pivot_table = '';
    form.pivot_foreign_key = '';
    form.target_foreign_key = '';
    form.filter_key = '';
    form.parent_column = '';
    form.additional_where = null;
    rawAdditionalWhere.value = '';
  } catch (err) {
    alert("Errore durante il salvataggio della regola di visibilità.");
  }
};

const deleteDefinition = async (id) => {
  if (confirm("Confermi la rimozione di questa regola di visibilità?")) {
    try {
      await filterService.deleteFilterDefinition(id);
      definitions.value = definitions.value.filter(d => d.id !== id);
    } catch (err) {
      alert("Errore durante la rimozione della regola.");
    }
  }
};

onMounted(fetchData);
</script>
