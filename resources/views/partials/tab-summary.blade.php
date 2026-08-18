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
          class="px-3.5 py-1.5 rounded-xl text-xs transition cursor-pointer"
        >
          Tutti (@{{ summaryStats.total }})
        </button>
        <button 
          @click="summaryStatusFilter = 'with_filters'"
          :class="[summaryStatusFilter === 'with_filters' ? 'bg-indigo-600 text-white font-bold' : 'bg-slate-100 text-slate-600 font-medium hover:bg-slate-200']"
          class="px-3.5 py-1.5 rounded-xl text-xs transition cursor-pointer"
        >
          Con Permessi (@{{ summaryStats.withFilters }})
        </button>
        <button 
          @click="summaryStatusFilter = 'without_filters'"
          :class="[summaryStatusFilter === 'without_filters' ? 'bg-indigo-600 text-white font-bold' : 'bg-slate-100 text-slate-600 font-medium hover:bg-slate-200']"
          class="px-3.5 py-1.5 rounded-xl text-xs transition cursor-pointer"
        >
          Senza Vincoli (@{{ summaryStats.withoutFilters }})
        </button>
      </div>

      <div class="relative w-full md:w-72">
        <input 
          v-model="summarySearchQuery" 
          type="text" 
          placeholder="Cerca per nome, email o ID..." 
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
              <div class="font-bold text-slate-900">@{{ u.label || u.name || 'Utente #' + u.id }}</div>
              <div class="text-[11px] text-slate-500 flex items-center gap-1.5 mt-0.5">
                <span v-if="u.email" class="text-indigo-600 font-medium">@{{ u.email }}</span>
                <span v-else-if="u.sublabel" class="text-slate-500 font-medium">@{{ u.sublabel }}</span>
                <span class="text-slate-300">•</span>
                <span class="text-slate-400">ID: @{{ u.id }}</span>
              </div>
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
                class="px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold rounded-xl transition cursor-pointer"
              >
                Configura
              </button>
              <button 
                v-if="u.has_filters"
                @click="goToCloneUser(u)"
                class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl transition cursor-pointer"
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
