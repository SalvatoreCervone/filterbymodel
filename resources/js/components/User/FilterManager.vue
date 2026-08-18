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
        
        <!-- Slot per la ricerca utente -->
        <slot name="user-search" :on-user-selected="handleUserSelected">
          <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-600 flex items-center justify-between">
            <span>Per collegare il componente di ricerca utente della tua applicazione, usa lo slot <code>#user-search="{ onUserSelected }"</code>.</span>
          </div>
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
import { filterService } from '../../services/filterService';

const props = defineProps({
  definitions: {
    type: Array,
    default: null
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

const handleUserSelected = async (userId) => {
  selectedUserId.value = userId;
  emit('user-selected', userId);

  if (userId) {
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
