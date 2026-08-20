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

            <!-- IDENTIFICATIVO PERSONALIZZATO DELLA SCHEDA PROTETTA (OPZIONALE) -->
            <div v-if="form.has_pivot" class="p-3.5 bg-amber-50/60 rounded-xl border border-amber-200 space-y-1.5">
              <div class="flex items-center justify-between">
                <label class="block text-xs font-extrabold text-slate-800">
                  Colonna Personalizzata del Modello da Proteggere (Opzionale)
                </label>
                <span class="text-[10px] font-bold text-amber-700 bg-amber-100 px-2 py-0.5 rounded">
                  Default: 'id'
                </span>
              </div>
              <p class="text-[11px] text-slate-600 leading-relaxed">
                Lascia vuoto se la scheda usa la chiave primaria standard (<code>id</code>). Compilalo se il collegamento punta a un'altra colonna della scheda (es. <code>codice</code>, <code>matricola</code>, <code>uuid</code>).
              </p>
              <input 
                v-model="form.target_foreign_key" 
                type="text" 
                placeholder="Lascia vuoto per usare il normale 'id' (Default)" 
                class="w-full border border-amber-300 rounded-xl p-2.5 text-xs font-mono font-medium bg-white focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600"
              >
            </div>

            <!-- FILTRI E CONDIZIONI ADDIZIONALI (VISUAL RULE BUILDER) -->
            <div class="pt-4 border-t border-slate-200 space-y-3">
              <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <div>
                  <div class="flex items-center gap-2">
                    <label class="block text-xs font-extrabold text-slate-900 uppercase tracking-wider">
                      Condizioni Aggiuntive & Parametri Extra (Opzionale)
                    </label>
                    <span v-if="isLoadingColumns" class="inline-flex items-center gap-1 text-[10px] text-indigo-600 font-semibold bg-indigo-50 px-2 py-0.5 rounded animate-pulse">
                      <svg class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                      Lettura colonne...
                    </span>
                  </div>
                  <p class="text-[11px] text-slate-500 mt-0.5">
                    Aggiungi condizioni fisse sul modello o sulla pivot (es. <code>stato = 'attivo'</code>, <code>importo > 1000</code>, <code>owner_id = @auth_id</code>).
                  </p>
                </div>

                <div class="flex items-center gap-2 self-start sm:self-auto">
                  <button 
                    type="button" 
                    @click="conditionMode = (conditionMode === 'visual' ? 'raw' : 'visual')"
                    class="text-[11px] px-2.5 py-1 rounded-lg border font-semibold transition cursor-pointer"
                    :class="[conditionMode === 'raw' ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-slate-100 text-slate-700 hover:bg-slate-200 border-slate-200']"
                  >
                    @{{ conditionMode === 'visual' ? '⚙️ Modalità JSON' : '👁️ Visual Builder' }}
                  </button>
                  <button 
                    type="button" 
                    @click="addCondition" 
                    class="text-xs px-3 py-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold rounded-lg transition border border-indigo-200 flex items-center gap-1 cursor-pointer"
                  >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Aggiungi Condizione</span>
                  </button>
                </div>
              </div>

              <!-- PRESET RAPIDI -->
              <div class="flex flex-wrap items-center gap-1.5 pt-1">
                <span class="text-[10px] font-bold text-slate-400 uppercase mr-1">Preset rapidi:</span>
                <button 
                  type="button" 
                  @click="addConditionPreset('stato', '=', 'attivo')" 
                  class="text-[11px] px-2 py-0.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium rounded-md transition border border-slate-200 cursor-pointer"
                >
                  + Solo Attivi (stato = attivo)
                </button>
                <button 
                  type="button" 
                  @click="addConditionPreset('anno', '>=', '@current_year')" 
                  class="text-[11px] px-2 py-0.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium rounded-md transition border border-slate-200 cursor-pointer"
                >
                  + Anno Corrente (@current_year)
                </button>
                <button 
                  type="button" 
                  @click="addConditionPreset('owner_id', '=', '@auth_id')" 
                  class="text-[11px] px-2 py-0.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium rounded-md transition border border-slate-200 cursor-pointer"
                >
                  + Utente Loggato (@auth_id)
                </button>
                <button 
                  type="button" 
                  @click="addConditionPreset('deleted_at', 'IS NULL', '')" 
                  class="text-[11px] px-2 py-0.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium rounded-md transition border border-slate-200 cursor-pointer"
                >
                  + Non Eliminati (deleted_at IS NULL)
                </button>
              </div>

              <!-- MODALITÀ VISUAL BUILDER (TABELLA A RIGHE) -->
              <div v-if="conditionMode === 'visual'" class="space-y-2.5">
                <div v-if="conditions.length === 0" class="p-4 rounded-xl border border-dashed border-slate-300 bg-slate-50/50 text-center">
                  <p class="text-xs text-slate-500">Nessuna condizione aggiuntiva impostata.</p>
                  <button 
                    type="button" 
                    @click="addCondition"
                    class="mt-1.5 text-xs text-indigo-600 font-bold hover:underline cursor-pointer"
                  >
                    + Clicca qui per aggiungere la prima regola
                  </button>
                </div>

                <div v-else class="space-y-2">
                  <div 
                    v-for="(cond, idx) in conditions" 
                    :key="idx"
                    class="p-2.5 bg-white rounded-xl border border-slate-200 shadow-xs flex flex-col sm:flex-row items-stretch sm:items-center gap-2"
                  >
                    <!-- COLONNA (SELECT A TENDINA CON TUTTE LE COLONNE DELLO SCHEMA DB) -->
                    <div class="flex-1 min-w-[140px]">
                      <label class="block sm:hidden text-[10px] font-bold text-slate-400 mb-0.5">Colonna</label>
                      <div class="relative">
                        <select 
                          v-if="availableColumns.length > 0 && !cond.isCustom"
                          v-model="cond.column" 
                          @change="onConditionColumnChange(cond)"
                          class="w-full border border-slate-300 rounded-lg px-2.5 py-1.5 text-xs font-mono font-semibold text-slate-800 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 bg-white"
                          required
                        >
                          <option value="" disabled>-- Seleziona Colonna --</option>
                          <option v-for="col in availableColumns" :key="col" :value="col">
                            @{{ col }}
                          </option>
                          <option value="__custom__">✏️ Digita colonna personalizzata...</option>
                        </select>
                        <div v-else class="flex items-center gap-1">
                          <input 
                            v-model="cond.column" 
                            type="text" 
                            placeholder="Nome colonna (es. stato)"
                            @input="syncConditionsToRawJson"
                            class="w-full border border-slate-300 rounded-lg px-2.5 py-1.5 text-xs font-mono font-medium focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 bg-white"
                            required
                          >
                          <button 
                            v-if="availableColumns.length > 0"
                            type="button" 
                            @click="resetConditionColumn(cond)"
                            class="px-2 py-1 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-lg text-xs font-bold transition cursor-pointer"
                            title="Torna all'elenco a tendina"
                          >
                            ✕
                          </button>
                        </div>
                      </div>
                    </div>

                    <!-- OPERATORE -->
                    <div class="w-full sm:w-[130px]">
                      <label class="block sm:hidden text-[10px] font-bold text-slate-400 mb-0.5">Segno / Operatore</label>
                      <select 
                        v-model="cond.operator" 
                        class="w-full border border-slate-300 rounded-lg px-2.5 py-1.5 text-xs font-semibold text-slate-800 bg-slate-50 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600"
                      >
                        <option value="=">Uguale (=)</option>
                        <option value="!=">Diverso (!=)</option>
                        <option value=">">Maggiore (&gt;)</option>
                        <option value=">=">Maggiore o Uguale (&gt;=)</option>
                        <option value="<">Minore (&lt;)</option>
                        <option value="<=">Minore o Uguale (&lt;=)</option>
                        <option value="LIKE">Contiene (LIKE)</option>
                        <option value="NOT LIKE">Non Contiene (NOT LIKE)</option>
                        <option value="IN">In Elenco (IN)</option>
                        <option value="NOT IN">Non In Elenco (NOT IN)</option>
                        <option value="IS NULL">È Nullo (IS NULL)</option>
                        <option value="IS NOT NULL">Non È Nullo (IS NOT NULL)</option>
                        <option value="BETWEEN">Compreso (BETWEEN)</option>
                      </select>
                    </div>

                    <!-- VALORE (COMBOBOX CON SUGGERIMENTI DINAMICI E VALORI DISTINCT CAMPIONATI) -->
                    <div class="flex-1 min-w-[140px]">
                      <div class="flex items-center justify-between sm:hidden mb-0.5">
                        <label class="text-[10px] font-bold text-slate-400">Valore</label>
                        <span v-if="isLoadingColumnValues[cond.column]" class="text-[9px] text-indigo-600 animate-pulse font-semibold">Caricamento valori...</span>
                      </div>
                      <div class="relative">
                        <input 
                          v-if="!['IS NULL', 'IS NOT NULL'].includes(cond.operator)"
                          v-model="cond.value" 
                          :list="'column-values-list-' + idx"
                          type="text" 
                          @focus="loadColumnValues(cond.column)"
                          @change="syncConditionsToRawJson"
                          :placeholder="cond.operator === 'IN' ? 'es. VIP, GOLD, SILVER' : (cond.operator === 'BETWEEN' ? 'es. 100, 500' : 'es. attivo o @auth_id')"
                          class="w-full border border-slate-300 rounded-lg px-2.5 py-1.5 text-xs font-mono font-medium focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600"
                        >
                        <div v-else class="text-xs text-slate-400 italic px-2 py-1.5 bg-slate-50 rounded-lg border border-slate-200">
                          (Nessun valore richiesto)
                        </div>

                        <!-- DATALIST VALORI SUGGERITI & DISTINCT DA DB -->
                        <datalist :id="'column-values-list-' + idx">
                          <!-- Segnaposto speciali sempre disponibili -->
                          <option value="@auth_id">@auth_id (ID Operatore Loggato)</option>
                          <option value="@current_year">@current_year (Anno Solare Corrente)</option>
                          <option value="@today">@today (Data Odierna)</option>
                          <option value="@null">@null (Valore Nullo)</option>
                          <!-- Valori univoci campionati dal database -->
                          <option v-for="val in (columnValuesMap[cond.column] || [])" :key="val" :value="val">
                            @{{ val }}
                          </option>
                        </datalist>
                      </div>
                    </div>

                    <!-- PULSANTE ELIMINA RIGA -->
                    <button 
                      type="button" 
                      @click="removeCondition(idx)" 
                      class="p-1.5 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition self-end sm:self-center cursor-pointer"
                      title="Elimina condizione"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                    </button>
                  </div>
              </div>

              <!-- MODALITÀ AVANZATA (RAW JSON) -->
              <div v-else class="space-y-2">
                <textarea 
                  v-model="rawAdditionalWhere" 
                  rows="3" 
                  class="w-full border rounded-xl p-2.5 text-xs font-mono bg-white focus:ring-2 shadow-xs transition" 
                  :class="[jsonError ? 'border-rose-400 focus:border-rose-500 focus:ring-rose-500/20 bg-rose-50/20' : 'border-slate-300 focus:border-indigo-500 focus:ring-indigo-500/20']"
                  placeholder='es. [{"column": "stato", "operator": "=", "value": "attivo"}]' 
                  @input="syncRawJsonToConditions"
                ></textarea>
                <p v-if="jsonError" class="text-[11px] text-rose-600 font-semibold">Formato JSON non valido. Correggi la sintassi prima di salvare.</p>
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
          <button @click="loadDefinitions" class="text-xs text-indigo-600 hover:text-indigo-800 font-semibold cursor-pointer">Aggiorna</button>
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
              <div class="text-[11px] text-slate-500 font-mono flex flex-wrap items-center gap-1.5 mt-0.5">
                <span>chiave: @{{ d.filter_key }}</span>
                <span v-if="d.pivot_table">• pivot: @{{ d.pivot_table }}</span>
                <span v-if="d.target_foreign_key" class="bg-amber-100 text-amber-800 text-[10px] px-1.5 py-0.2 rounded font-mono">chiave scheda: @{{ d.target_foreign_key }}</span>
                <span v-if="d.additional_where" class="bg-indigo-100 text-indigo-800 text-[10px] px-1.5 py-0.2 rounded font-mono">+filtri extra</span>
              </div>
            </div>

            <button 
              @click="deleteDefinition(d.id)"
              class="p-2 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition cursor-pointer"
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
