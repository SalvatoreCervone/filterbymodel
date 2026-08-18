<template>
  <div class="min-h-screen bg-slate-50/70 p-6 sm:p-10 font-sans text-slate-800">
    <div class="max-w-7xl mx-auto space-y-8">
      
      <!-- HEADER CON NAVIGAZIONE TAB (CONFIGURAZIONE vs RESOCONTO) -->
      <div class="bg-white rounded-2xl border border-slate-200/80 p-6 sm:p-8 shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
          <div class="p-2.5 bg-indigo-50 text-indigo-600 rounded-xl border border-indigo-100/80">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
          </div>
          <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Gestione Competenze e Filtri Utente</h1>
            <p class="text-xs sm:text-sm text-slate-500 font-medium">Assegna, clona e monitora i perimetri di autorizzazione per tutti gli operatori.</p>
          </div>
        </div>

        <!-- SELETTORE TAB -->
        <div class="flex items-center gap-1 bg-slate-100 p-1.5 rounded-xl border border-slate-200">
          <button
            type="button"
            @click="activeView = 'manager'"
            :class="[
              'px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center gap-1.5',
              activeView === 'manager' ? 'bg-white text-indigo-700 shadow-xs' : 'text-slate-600 hover:text-slate-900'
            ]"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
            <span>Configura Operatore</span>
          </button>

          <button
            type="button"
            @click="activeView = 'summary'"
            :class="[
              'px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center gap-1.5',
              activeView === 'summary' ? 'bg-white text-indigo-700 shadow-xs' : 'text-slate-600 hover:text-slate-900'
            ]"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <span>Resoconto Globale</span>
          </button>
        </div>
      </div>

      <!-- VISTA 1: CONFIGURA OPERATORE -->
      <div v-if="activeView === 'manager'" class="space-y-6">
        
        <!-- LIVELLO SUPERIORE: RICERCA / SELEZIONE UTENTE -->
        <div class="bg-white p-6 sm:p-7 rounded-2xl border border-slate-200 shadow-sm space-y-4">
          <div class="border-b border-slate-100 pb-3 flex items-center justify-between">
            <div>
              <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">1. Seleziona Operatore</h3>
              <p class="text-xs text-slate-500 mt-0.5">Cerca un utente di sistema per configurarne le restrizioni e i permessi sui dati.</p>
            </div>
            <div v-if="selectedUserId" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-indigo-50 border border-indigo-100 text-indigo-700 text-xs font-bold">
              <span class="w-2 h-2 rounded-full bg-indigo-600"></span>
              ID Utente: {{ selectedUserId }}
            </div>
          </div>
          
          <!-- Slot per la ricerca utente con Autocomplete integrato di default -->
          <slot 
            name="user-search" 
            :on-user-selected="handleUserSelected"
            :selected-id="selectedUserId"
          >
            <UserAutocomplete
              :model-value="selectedUserId"
              :table="userTable"
              :id-field="userIdField"
              :label-field="userLabelField"
              :placeholder="placeholder"
              @update:model-value="handleUserSelected"
            />
          </slot>
        </div>

      <!-- NOTIFICA FEEDBACK OPERAZIONI (TOAST/ALERT) -->
      <div 
        v-if="notification" 
        class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl flex items-center justify-between gap-3 animate-in fade-in slide-in-from-top-2 duration-200 shadow-xs"
      >
        <div class="flex items-center gap-2.5 text-xs font-semibold">
          <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <span>{{ notification }}</span>
        </div>
        <button 
          type="button" 
          @click="notification = ''" 
          class="text-emerald-500 hover:text-emerald-700 p-1 rounded-lg hover:bg-emerald-100/60 transition-colors"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- SEZIONE VISIBILE SOLO DOPO AVER SELEZIONATO UN UTENTE -->
      <div v-if="selectedUserId" class="space-y-6 transition-all">
        
        <!-- BARRA AZIONI OPERATORE: CLONA COMPETENZE -->
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 p-4 bg-white border border-slate-200 rounded-2xl shadow-xs">
          <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-700 flex items-center justify-center font-bold text-xs">
              {{ userFilters.length }}
            </div>
            <div>
              <p class="text-xs font-bold text-slate-800">Competenze Assegnate</p>
              <p class="text-[11px] text-slate-500">Regole di visibilità e perimetri attivi per questo operatore.</p>
            </div>
          </div>

          <!-- Pulsante Clona su Altri Utenti -->
          <button
            type="button"
            @click="isCopyModalOpen = true"
            :disabled="userFilters.length === 0"
            :class="[
              'px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 shadow-2xs',
              userFilters.length > 0
                ? 'bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white shadow-indigo-200 hover:shadow-indigo-300'
                : 'bg-slate-100 text-slate-400 border border-slate-200 cursor-not-allowed'
            ]"
            title="Copia le autorizzazioni e i filtri di questo operatore su uno o più altri utenti"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" />
            </svg>
            <span>Clona Competenze su altri Utenti</span>
          </button>
        </div>

        <!-- Tabella con la lista dei filtri attivi -->
        <FilterList 
          :filters="userFilters" 
          :loading="loadingList" 
          @filter-deleted="handleDeleted" 
        />

        <!-- Form per aggiungere nuovi vincoli/filtri -->
        <FilterForm 
          :definitions="resolvedDefinitions" 
          :selected-user-id="selectedUserId"
          @filter-created="fetchUserFilters" 
        />
      </div>

      <!-- GUIDA QUANDO NESSUN UTENTE È SELEZIONATO -->
      <div v-else class="bg-indigo-50/50 border border-indigo-100 rounded-2xl p-8 text-center text-slate-600 text-sm space-y-2">
        <svg class="w-8 h-8 mx-auto text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
        </svg>
        <p class="font-semibold text-slate-800">Seleziona prima un utente</p>
        <p class="text-xs text-slate-500 max-w-md mx-auto">
          Utilizza la ricerca in alto per caricare il profilo dell'operatore e gestire i suoi perimetri di visibilità sui modelli protetti.
        </p>
      </div>

      </div>

      <!-- VISTA 2: RESOCONTO GLOBALE UTENTI (DATATABLE CON STATO FILTRI) -->
      <div v-else-if="activeView === 'summary'">
        <UserSummaryTable
          :table="userTable"
          :id-field="userIdField"
          :label-field="userLabelField"
          @select-user="handleSelectFromSummary"
          @clone-user="handleCloneFromSummary"
          ref="summaryTableRef"
        />
      </div>

      <!-- MODALE COPIA E CLONA COMPETENZE -->
      <CopyFiltersModal
        v-if="selectedUserId"
        :is-open="isCopyModalOpen"
        :source-user-id="selectedUserId"
        :source-filters="userFilters"
        :table="userTable"
        :id-field="userIdField"
        :label-field="userLabelField"
        @close="isCopyModalOpen = false"
        @copied="handleCopiedSuccess"
      />

    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import FilterForm from './FilterForm.vue';
import FilterList from './FilterList.vue';
import UserAutocomplete from './UserAutocomplete.vue';
import CopyFiltersModal from './CopyFiltersModal.vue';
import UserSummaryTable from './UserSummaryTable.vue';
import { filterService } from '../../services/filterService';

const props = defineProps({
  /** Definizioni di filtro pre-caricate (opzionale) */
  definitions: {
    type: Array,
    default: null
  },
  /** Tabella database per l'autocomplete di ricerca (default: 'users') */
  userTable: {
    type: String,
    default: 'users'
  },
  /** Nome colonna ID per l'autocomplete di ricerca (default: 'id') */
  userIdField: {
    type: String,
    default: 'id'
  },
  /** Nome colonna etichetta per l'autocomplete di ricerca (default: 'name') */
  userLabelField: {
    type: String,
    default: 'name'
  },
  /** Testo placeholder dell'autocomplete */
  placeholder: {
    type: String,
    default: 'Cerca operatore per nome, email o ID...'
  }
});

const emit = defineEmits(['user-selected', 'filters-cloned']);

const activeView = ref('manager'); // 'manager' (configurazione) o 'summary' (resoconto)
const selectedUserId = ref(null);
const userFilters = ref([]);
const loadingList = ref(false);
const fetchedDefinitions = ref([]);
const isCopyModalOpen = ref(false);
const notification = ref('');
const summaryTableRef = ref(null);

const resolvedDefinitions = computed(() => {
  return props.definitions || fetchedDefinitions.value;
});

/**
 * Gestisce la selezione dell'operatore.
 * Restituisce ed emette RIGOROSAMENTE il solo ID (mai un oggetto).
 */
const handleUserSelected = async (userOrId) => {
  const id = (typeof userOrId === 'object' && userOrId !== null) 
    ? (userOrId.id ?? userOrId.user_id ?? userOrId.value ?? null) 
    : (userOrId || null);

  selectedUserId.value = id;
  notification.value = '';
  // Emette rigorosamente il solo ID verso il genitore
  emit('user-selected', id);

  if (id) {
    await fetchUserFilters();
  } else {
    userFilters.value = [];
  }
};

const handleSelectFromSummary = async (userId) => {
  activeView.value = 'manager';
  await handleUserSelected(userId);
};

const handleCloneFromSummary = async (user) => {
  await handleUserSelected(user.id);
  isCopyModalOpen.value = true;
};

const fetchUserFilters = async () => {
  if (!selectedUserId.value) return;
  
  loadingList.value = true;
  try {
    userFilters.value = await filterService.getUserFilters(selectedUserId.value);
  } catch (err) {
    console.error("Impossibile caricare i filtri dell'utente", err);
  } finally {
    loadingList.value = false;
  }
};

const handleDeleted = (deletedId) => {
  userFilters.value = userFilters.value.filter(f => f.id !== deletedId);
  if (summaryTableRef.value && typeof summaryTableRef.value.fetchSummary === 'function') {
    summaryTableRef.value.fetchSummary();
  }
};

const handleCopiedSuccess = (result) => {
  notification.value = result.message || 'Competenze clonate con successo!';
  emit('filters-cloned', result);
  if (summaryTableRef.value && typeof summaryTableRef.value.fetchSummary === 'function') {
    summaryTableRef.value.fetchSummary();
  }
};

onMounted(async () => {
  if (!props.definitions) {
    try {
      fetchedDefinitions.value = await filterService.getFilterDefinitions();
    } catch (err) {
      console.error("Errore nel caricamento delle definizioni di filtro", err);
    }
  }
});

defineExpose({ handleUserSelected, activeView });
</script>
