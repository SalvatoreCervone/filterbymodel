<template>
  <div class="min-h-screen bg-slate-50/70 p-6 sm:p-10 font-sans text-slate-800">
    <div class="max-w-7xl mx-auto space-y-8">
      
      <!-- HEADER -->
      <div class="bg-white rounded-2xl border border-slate-200/80 p-6 sm:p-8 shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
          <div class="p-2.5 bg-indigo-50 text-indigo-600 rounded-xl border border-indigo-100/80">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
          </div>
          <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Gestione Competenze e Filtri Utente</h1>
            <p class="text-xs sm:text-sm text-slate-500 font-medium">Assegna e gestisci i perimetri di autorizzazione su uffici, ruoli o qualifiche per ciascun utente.</p>
          </div>
        </div>
      </div>

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

      <!-- SEZIONE VISIBILE SOLO DOPO AVER SELEZIONATO UN UTENTE -->
      <div v-if="selectedUserId" class="space-y-6 transition-all">
        
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
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import FilterForm from './FilterForm.vue';
import FilterList from './FilterList.vue';
import UserAutocomplete from './UserAutocomplete.vue';
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

const emit = defineEmits(['user-selected']);

const selectedUserId = ref(null);
const userFilters = ref([]);
const loadingList = ref(false);
const fetchedDefinitions = ref([]);

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
  // Emette rigorosamente il solo ID verso il genitore
  emit('user-selected', id);

  if (id) {
    await fetchUserFilters();
  } else {
    userFilters.value = [];
  }
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

defineExpose({ handleUserSelected });
</script>
