<template>
  <div class="space-y-6">
    
    <!-- STAT CARDS IN ALTO -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
      
      <!-- Card 1: Totale Utenti -->
      <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
        <div class="space-y-1">
          <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Totale Operatori</p>
          <p class="text-2xl font-black text-slate-900">{{ summaryData.total_users || 0 }}</p>
        </div>
        <div class="p-3 bg-slate-100 text-slate-600 rounded-xl">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
          </svg>
        </div>
      </div>

      <!-- Card 2: Con Filtri Bindati -->
      <div 
        @click="statusFilter = 'with_filters'; handleSearch();"
        class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between cursor-pointer hover:border-emerald-300 transition-all group"
        :class="{ 'ring-2 ring-emerald-400 bg-emerald-50/20': statusFilter === 'with_filters' }"
      >
        <div class="space-y-1">
          <div class="flex items-center gap-1.5">
            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Con Permessi Bindati</p>
          </div>
          <p class="text-2xl font-black text-emerald-600">{{ summaryData.total_with_filters || 0 }}</p>
        </div>
        <div class="p-3 bg-emerald-50 text-emerald-600 rounded-xl group-hover:bg-emerald-100 transition-colors">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
          </svg>
        </div>
      </div>

      <!-- Card 3: Senza Filtri -->
      <div 
        @click="statusFilter = 'without_filters'; handleSearch();"
        class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between cursor-pointer hover:border-slate-400 transition-all group"
        :class="{ 'ring-2 ring-slate-400 bg-slate-50/60': statusFilter === 'without_filters' }"
      >
        <div class="space-y-1">
          <div class="flex items-center gap-1.5">
            <span class="w-2 h-2 rounded-full bg-slate-400"></span>
            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Senza Vincoli (Liberi)</p>
          </div>
          <p class="text-2xl font-black text-slate-700">{{ summaryData.total_without_filters || 0 }}</p>
        </div>
        <div class="p-3 bg-slate-100 text-slate-500 rounded-xl group-hover:bg-slate-200 transition-colors">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z" />
          </svg>
        </div>
      </div>

    </div>

    <!-- DATATABLE CONTAINER -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden space-y-4">
      
      <!-- TOOLBAR: FILTRI STATO + SEARCH INPUT -->
      <div class="p-5 sm:p-6 border-b border-slate-100 flex flex-col md:flex-row items-start md:items-center justify-between gap-4 bg-slate-50/50">
        
        <!-- Pulsanti di filtro rapido -->
        <div class="flex items-center gap-1.5 bg-slate-200/70 p-1 rounded-xl">
          <button
            type="button"
            @click="setStatusFilter('all')"
            :class="[
              'px-3 py-1.5 rounded-lg text-xs font-bold transition-all',
              statusFilter === 'all' ? 'bg-white text-slate-900 shadow-2xs' : 'text-slate-600 hover:text-slate-900'
            ]"
          >
            Tutti ({{ summaryData.total_users || 0 }})
          </button>
          <button
            type="button"
            @click="setStatusFilter('with_filters')"
            :class="[
              'px-3 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center gap-1.5',
              statusFilter === 'with_filters' ? 'bg-emerald-600 text-white shadow-2xs' : 'text-emerald-700 hover:text-emerald-900'
            ]"
          >
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
            Con Permessi ({{ summaryData.total_with_filters || 0 }})
          </button>
          <button
            type="button"
            @click="setStatusFilter('without_filters')"
            :class="[
              'px-3 py-1.5 rounded-lg text-xs font-bold transition-all',
              statusFilter === 'without_filters' ? 'bg-slate-700 text-white shadow-2xs' : 'text-slate-600 hover:text-slate-900'
            ]"
          >
            Senza Vincoli ({{ summaryData.total_without_filters || 0 }})
          </button>
        </div>

        <!-- Barra di ricerca e pulsante ricarica -->
        <div class="flex items-center gap-2 w-full md:w-auto">
          <div class="relative flex-1 md:w-64">
            <input
              type="text"
              v-model="searchQuery"
              @input="handleSearch"
              placeholder="Filtra per nome o ID..."
              class="w-full pl-9 pr-4 py-2 bg-white border border-slate-300 rounded-xl text-xs text-slate-800 placeholder-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 outline-none transition"
            />
            <div class="absolute left-3 top-2.5 text-slate-400 pointer-events-none">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </div>
          </div>

          <button
            type="button"
            @click="fetchSummary"
            class="p-2 rounded-xl border border-slate-300 hover:bg-slate-100 text-slate-600 transition-colors"
            title="Ricarica resoconto"
          >
            <svg class="w-4 h-4" :class="{ 'animate-spin': loading }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
          </button>
        </div>

      </div>

      <!-- TABELLA UTENTI -->
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-slate-50/80 border-b border-slate-200 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
              <th class="px-6 py-3.5">Operatore</th>
              <th class="px-6 py-3.5">Stato Sicurezza</th>
              <th class="px-6 py-3.5">Competenze Bindate</th>
              <th class="px-6 py-3.5 text-right">Azioni</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
            
            <!-- STATO CARICAMENTO -->
            <tr v-if="loading">
              <td colspan="4" class="px-6 py-12 text-center text-slate-400">
                <div class="flex items-center justify-center gap-2">
                  <svg class="animate-spin h-5 w-5 text-indigo-600" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  <span class="text-xs font-semibold text-slate-600">Caricamento resoconto in corso...</span>
                </div>
              </td>
            </tr>

            <!-- NESSUN RISULTATO -->
            <tr v-else-if="usersList.length === 0">
              <td colspan="4" class="px-6 py-12 text-center text-slate-400 text-xs">
                Nessun operatore trovato per i filtri selezionati.
              </td>
            </tr>

            <!-- RIGHE DATATABLE -->
            <tr 
              v-else 
              v-for="user in usersList" 
              :key="user.id"
              class="hover:bg-slate-50/80 transition-colors"
            >
              <!-- 1. Info Utente -->
              <td class="px-6 py-4">
                <div class="flex items-center gap-3">
                  <div 
                    :class="[
                      'w-9 h-9 rounded-xl font-bold text-xs flex items-center justify-center shadow-2xs flex-shrink-0',
                      user.has_filters ? 'bg-indigo-600 text-white' : 'bg-slate-200 text-slate-600'
                    ]"
                  >
                    {{ getInitials(user.label) }}
                  </div>
                  <div>
                    <div class="flex items-center gap-2">
                      <p class="font-bold text-slate-900 text-sm">{{ user.label }}</p>
                      <span class="text-[10px] font-mono font-bold px-1.5 py-0.5 rounded bg-slate-100 text-slate-500">
                        ID: {{ user.id }}
                      </span>
                    </div>
                    <p v-if="user.sublabel" class="text-xs text-slate-400">
                      {{ user.sublabel }}
                    </p>
                  </div>
                </div>
              </td>

              <!-- 2. Badge Stato -->
              <td class="px-6 py-4">
                <span 
                  v-if="user.has_filters" 
                  class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 border border-emerald-200 text-emerald-700"
                >
                  <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                  </svg>
                  <span>{{ user.filters_count }} {{ user.filters_count === 1 ? 'Filtro' : 'Filtri' }} ({{ user.groups_count }} {{ user.groups_count === 1 ? 'Gruppo' : 'Gruppi' }})</span>
                </span>
                <span 
                  v-else 
                  class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 border border-slate-200 text-slate-500"
                >
                  <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z" />
                  </svg>
                  <span>Nessun vincolo (Accesso libero)</span>
                </span>
              </td>

              <!-- 3. Dettaglio Competenze -->
              <td class="px-6 py-4">
                <div v-if="user.filters_summary && user.filters_summary.length > 0" class="flex flex-wrap gap-1.5">
                  <span 
                    v-for="sum in user.filters_summary" 
                    :key="sum.type"
                    class="px-2 py-0.5 rounded-lg text-[11px] font-semibold bg-indigo-50 border border-indigo-100 text-indigo-700"
                  >
                    {{ sum.name }}: <strong class="font-bold">{{ sum.count }}</strong>
                  </span>
                </div>
                <span v-else class="text-xs text-slate-400 italic">
                  —
                </span>
              </td>

              <!-- 4. Azioni -->
              <td class="px-6 py-4 text-right">
                <div class="flex items-center justify-end gap-2">
                  <button
                    type="button"
                    @click="$emit('select-user', user.id)"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 border border-indigo-100 transition-colors shadow-2xs"
                  >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    <span>Configura</span>
                  </button>

                  <button
                    v-if="user.has_filters"
                    type="button"
                    @click="$emit('clone-user', user)"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 border border-slate-200 transition-colors shadow-2xs"
                    title="Clona permessi su altri colleghi"
                  >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" />
                    </svg>
                    <span>Clona</span>
                  </button>
                </div>
              </td>

            </tr>

          </tbody>
        </table>
      </div>

    </div>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { filterService } from '../../services/filterService';

const props = defineProps({
  table: {
    type: String,
    default: 'users'
  },
  idField: {
    type: String,
    default: 'id'
  },
  labelField: {
    type: String,
    default: 'name'
  }
});

const emit = defineEmits(['select-user', 'clone-user']);

const summaryData = ref({
  data: [],
  total_users: 0,
  total_with_filters: 0,
  total_without_filters: 0
});

const usersList = ref([]);
const loading = ref(false);
const searchQuery = ref('');
const statusFilter = ref('all'); // 'all', 'with_filters', 'without_filters'

let searchTimeout = null;

const fetchSummary = async () => {
  loading.value = true;
  try {
    const res = await filterService.getUserFiltersSummary({
      q: searchQuery.value,
      status: statusFilter.value,
      table: props.table,
      id_field: props.idField,
      label_field: props.labelField
    });
    summaryData.value = res;
    usersList.value = res.data || [];
  } catch (err) {
    console.error('Errore nel caricamento del resoconto utenti:', err);
    usersList.value = [];
  } finally {
    loading.value = false;
  }
};

const setStatusFilter = (status) => {
  statusFilter.value = status;
  fetchSummary();
};

const handleSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    fetchSummary();
  }, 250);
};

const getInitials = (text) => {
  if (!text) return '?';
  const parts = String(text).trim().split(/\s+/);
  if (parts.length >= 2) {
    return (parts[0][0] + parts[1][0]).toUpperCase();
  }
  return String(text).slice(0, 2).toUpperCase();
};

onMounted(() => {
  fetchSummary();
});

defineExpose({ fetchSummary });
</script>
