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
        placeholder="Cerca per nome, cognome, email o ID utente..."
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
          <div class="space-y-0.5">
            <div class="text-xs font-bold text-slate-900 flex items-center gap-2">
              <span>@{{ u.label || u.name || 'Utente #' + u.id }}</span>
              <span class="text-[10px] text-slate-400 font-normal">ID: @{{ u.id }}</span>
            </div>
            <div v-if="u.sublabel || u.email" class="text-[11px] text-slate-500 font-medium flex items-center gap-1.5">
              <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
              <span>@{{ u.sublabel || u.email }}</span>
            </div>
          </div>
          <span class="text-[10px] bg-slate-100 text-slate-600 px-2 py-0.5 rounded font-semibold">Seleziona</span>
        </div>
      </div>
    </div>

    <!-- CARD OPERATORE SELEZIONATO -->
    <div v-if="selectedUser" class="p-4 bg-indigo-50/70 border border-indigo-200 rounded-xl flex items-center justify-between">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-full bg-indigo-600 text-white font-black flex items-center justify-center text-sm shadow-xs">
          @{{ ((selectedUser.label || selectedUser.name || selectedUser.email || 'U').charAt(0)).toUpperCase() }}
        </div>
        <div>
          <div class="text-sm font-extrabold text-slate-900">@{{ selectedUser.label || selectedUser.name || 'Utente #' + selectedUser.id }}</div>
          <div class="text-xs text-slate-500 font-medium flex items-center gap-2 mt-0.5">
            <span v-if="selectedUser.email" class="text-indigo-600 font-semibold flex items-center gap-1">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
              @{{ selectedUser.email }}
            </span>
            <span v-else-if="selectedUser.sublabel" class="text-indigo-600 font-semibold">@{{ selectedUser.sublabel }}</span>
            <span class="text-slate-300">•</span>
            <span class="text-slate-500">ID Database: @{{ selectedUser.id }}</span>
          </div>
        </div>
      </div>
      
      <button 
        @click="openCloneModal"
        class="px-4 py-2 bg-white hover:bg-slate-100 border border-slate-300 text-slate-800 text-xs font-bold rounded-xl shadow-xs transition flex items-center gap-1.5 cursor-pointer"
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
          
          <div v-if="definitions.length === 0" class="p-3 bg-amber-50 border border-amber-200 text-amber-800 rounded-xl text-xs flex items-center gap-2">
            <svg class="w-4 h-4 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <span>Nessuna regola configurata. Crea prima un'associazione nella scheda <strong>Regole Modelli</strong>.</span>
          </div>

          <select 
            v-else
            v-model="userForm.scope_filter" 
            @change="onScopeFilterChange"
            class="w-full border border-slate-300 rounded-xl p-2.5 text-xs font-semibold text-slate-800 focus:border-indigo-600 focus:ring-2 focus:ring-indigo-600/20"
            required
          >
            <option value="">-- Seleziona Criterio (es. Ufficio, Sede, Qualifica...) --</option>
            <option v-for="crit in availableCriteria" :key="crit.scope_filter" :value="crit.scope_filter">
              @{{ crit.name }} (protegge: @{{ crit.target_models.join(', ') }})
            </option>
          </select>
        </div>

        <!-- AMBITO DI VALIDITÀ DELLA COMPETENZA (GLOBALE O MODELLO SPECIFICO) -->
        <div v-if="userForm.scope_filter">
          <label class="block text-xs font-bold text-slate-700 mb-1">Ambito di Validità</label>
          <select 
            v-model="userForm.target_model" 
            class="w-full border border-slate-300 rounded-xl p-2.5 text-xs font-semibold text-slate-800 focus:border-indigo-600 focus:ring-2 focus:ring-indigo-600/20 bg-slate-50/50"
          >
            <option value="">Tutte le schede collegate (Globale)</option>
            <option v-for="m in currentScopeTargetModels" :key="m.class" :value="m.class">
              Solo per @{{ m.name }}
            </option>
          </select>
          <p class="text-[10px] text-slate-400 mt-1">Scegli se la competenza vale ovunque o solo per una specifica scheda.</p>
        </div>

        <div>
          <div class="flex items-center justify-between mb-1">
            <label class="block text-xs font-bold text-slate-700">Elemento / Valore Autorizzato</label>
            <span v-if="isLoadingCriteriaItems" class="text-[10px] text-indigo-600 font-semibold animate-pulse">Caricamento opzioni...</span>
          </div>
          <div class="relative">
            <input 
              v-model="userForm.filterable_id" 
              list="criteria-items-datalist"
              type="text" 
              placeholder="es. 5 oppure seleziona dal menu a tendina..."
              class="w-full border border-slate-300 rounded-xl p-2.5 text-xs font-mono font-medium focus:border-indigo-600 focus:ring-2 focus:ring-indigo-600/20"
              required
            >
            <datalist id="criteria-items-datalist">
              <option v-for="item in criteriaItemsList" :key="item.id" :value="item.id">
                @{{ item.display || (item.label ? 'ID ' + item.id + ' — ' + item.label : item.id) }}
              </option>
            </datalist>
          </div>
          <p class="text-[10px] text-slate-400 mt-1">
            Seleziona l'elemento reale dal menu a tendina (con nome) oppure digita direttamente l'ID numerico.
          </p>
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
                class="w-4 h-4 text-indigo-600 rounded border-slate-300 focus:ring-indigo-500 cursor-pointer"
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
          class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold text-xs rounded-xl shadow-xs transition cursor-pointer"
        >
          Assegna Competenza
        </button>
      </form>
    </div>

    <!-- LISTA FILTRI ATTIVI UTENTE -->
    <div class="lg:col-span-7 bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4">
      <div class="flex items-center justify-between border-b border-slate-100 pb-3">
        <h3 class="text-sm font-bold text-slate-900">Competenze Assegnate (@{{ currentUserFilters.length }})</h3>
        <span class="text-xs text-slate-500">Operatore: @{{ selectedUser.label || selectedUser.name || '#' + selectedUser.id }}</span>
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
          <div class="space-y-1.5 text-xs">
            <div class="flex items-center gap-2 flex-wrap">
              <span class="font-extrabold text-slate-900">@{{ formatClassName(f.filterable_type) }}</span>
              <span class="px-2 py-0.5 bg-indigo-100 text-indigo-800 rounded font-mono font-bold text-[11px]">ID: @{{ f.filterable_id }}</span>
              <span class="px-2 py-0.5 bg-slate-200 text-slate-700 rounded text-[10px] font-bold">Gruppo @{{ f.group }}</span>

              <!-- Badge Ambito Specifico / Globale -->
              <span 
                v-if="f.target_model" 
                class="px-2 py-0.5 bg-amber-100 text-amber-800 border border-amber-200 rounded text-[10px] font-bold"
              >
                Solo per @{{ formatClassName(f.target_model) }}
              </span>
              <span 
                v-else 
                class="px-2 py-0.5 bg-indigo-50 text-indigo-700 border border-indigo-100 rounded text-[10px] font-semibold"
              >
                Globale
              </span>
            </div>

            <!-- Modelli governati/protetti da questa competenza -->
            <div class="flex items-center gap-1.5 flex-wrap text-[11px] text-slate-600">
              <span class="text-slate-400 font-medium">Protegge:</span>
              <span 
                v-if="f.target_model" 
                class="px-2 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-md font-semibold text-[10px]"
              >
                @{{ formatClassName(f.target_model) }}
              </span>
              <template v-else>
                <span 
                  v-for="m in getTargetModelsForScope(f.filterable_type)" 
                  :key="m" 
                  class="px-2 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-md font-semibold text-[10px]"
                >
                  @{{ m }}
                </span>
                <span v-if="getTargetModelsForScope(f.filterable_type).length === 0" class="text-slate-400 italic text-[10px]">
                  Nessuna scheda associata
                </span>
              </template>
            </div>

            <div v-if="f.include_children" class="text-[11px] text-emerald-700 font-semibold flex items-center gap-1">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
              Include tutti i sotto-nodi gerarchici discendenti
            </div>
          </div>

          <button 
            @click="deleteUserFilter(f.id)"
            class="p-2 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition cursor-pointer"
            title="Revoca competenza"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
          </button>
        </div>
      </div>
    </div>

  </div>
</main>
