<template>
  <div class="relative w-full" ref="containerRef">
    <!-- STATO 1: NESSUN UTENTE SELEZIONATO (CAMPO DI RICERCA AUTOCOMPLETE) -->
    <div v-if="!selectedItem && !selectedId">
      <div class="relative flex items-center">
        <!-- Icona Lente -->
        <div class="absolute left-3.5 text-slate-400 pointer-events-none flex items-center justify-center">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
        </div>

        <!-- Input di ricerca -->
        <input
          type="text"
          v-model="searchQuery"
          :placeholder="placeholder"
          @focus="handleFocus"
          @input="handleInput"
          @keydown="handleKeyDown"
          class="w-full pl-10 pr-10 py-2.5 bg-white border border-slate-300 hover:border-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 rounded-xl text-sm text-slate-800 placeholder-slate-400 transition-all outline-none shadow-sm"
          autocomplete="off"
        />

        <!-- Spinner di caricamento o pulsante pulisci -->
        <div class="absolute right-3 flex items-center gap-1.5">
          <svg v-if="loading" class="animate-spin h-4 w-4 text-indigo-500" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          <button
            v-else-if="searchQuery"
            type="button"
            @click="clearSearch"
            class="text-slate-400 hover:text-slate-600 p-0.5 rounded-full hover:bg-slate-100 transition-colors"
          >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>

      <!-- DROPDOWN DEI RISULTATI -->
      <div
        v-if="isOpen"
        class="absolute z-50 left-0 right-0 mt-1.5 bg-white border border-slate-200 rounded-xl shadow-xl overflow-hidden max-h-64 overflow-y-auto animate-in fade-in slide-in-from-top-1 duration-150"
      >
        <!-- Lista opzioni -->
        <ul v-if="results.length > 0" class="py-1 divide-y divide-slate-50">
          <li
            v-for="(item, index) in results"
            :key="item.id"
            @click="selectItem(item)"
            @mouseenter="highlightedIndex = index"
            :class="[
              'px-3.5 py-2.5 cursor-pointer flex items-center justify-between gap-3 transition-colors text-sm',
              highlightedIndex === index ? 'bg-indigo-50/80 text-indigo-900' : 'hover:bg-slate-50 text-slate-700'
            ]"
          >
            <div class="flex items-center gap-2.5 min-w-0">
              <!-- Avatar iniziale -->
              <div class="w-7 h-7 rounded-lg bg-indigo-100/70 text-indigo-700 font-bold text-xs flex items-center justify-center flex-shrink-0">
                {{ getInitials(item.label) }}
              </div>
              <div class="truncate">
                <p class="font-medium text-slate-800 truncate" :class="{ 'text-indigo-900 font-semibold': highlightedIndex === index }">
                  {{ item.label }}
                </p>
                <p v-if="item.sublabel" class="text-xs text-slate-400 truncate">
                  {{ item.sublabel }}
                </p>
              </div>
            </div>
            <span class="text-[11px] font-mono font-semibold px-2 py-0.5 rounded-md bg-slate-100 text-slate-500 flex-shrink-0">
              ID: {{ item.id }}
            </span>
          </li>
        </ul>

        <!-- Nessun risultato -->
        <div v-else-if="!loading" class="p-4 text-center text-xs text-slate-500">
          <p class="font-medium text-slate-600">Nessun operatore trovato</p>
          <p class="text-slate-400 mt-0.5">Prova con un altro termine o verifica la tabella/campo specificati.</p>
        </div>
      </div>
    </div>

    <!-- STATO 2: UTENTE SELEZIONATO (BADGE COMPATTO CON PULSANTE RESET) -->
    <div
      v-else
      class="flex items-center justify-between gap-3 p-3 bg-indigo-50/60 border border-indigo-200/70 rounded-xl"
    >
      <div class="flex items-center gap-3 min-w-0">
        <div class="w-9 h-9 rounded-xl bg-indigo-600 text-white font-bold text-sm flex items-center justify-center flex-shrink-0 shadow-sm">
          {{ getInitials(selectedItem?.label || String(selectedId)) }}
        </div>
        <div class="truncate">
          <div class="flex items-center gap-2">
            <h4 class="font-bold text-slate-900 text-sm truncate">
              {{ selectedItem?.label || `Operatore #${selectedId}` }}
            </h4>
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-100 text-indigo-700">
              ID: {{ selectedId }}
            </span>
          </div>
          <p v-if="selectedItem?.sublabel" class="text-xs text-indigo-600/80 truncate">
            {{ selectedItem.sublabel }}
          </p>
        </div>
      </div>

      <button
        type="button"
        @click="resetSelection"
        class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-semibold text-slate-600 hover:text-red-600 hover:bg-red-50 border border-slate-200 hover:border-red-200 bg-white transition-all shadow-2xs flex-shrink-0"
        title="Cambia o rimuovi operatore selezionato"
      >
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
        <span>Cambia</span>
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted, onBeforeUnmount } from 'vue';
import { filterService } from '../../services/filterService';

const props = defineProps({
  /** Valore selezionato (obbligatoriamente ID numerico o stringa) */
  modelValue: {
    type: [Number, String],
    default: null
  },
  /** Tabella database su cui eseguire la ricerca (default: 'users') */
  table: {
    type: String,
    default: 'users'
  },
  /** Nome della colonna chiave primaria da restituire come ID (default: 'id') */
  idField: {
    type: String,
    default: 'id'
  },
  /** Nome della colonna da usare come etichetta principale di ricerca (default: 'name') */
  labelField: {
    type: String,
    default: 'name'
  },
  /** Testo segnaposto */
  placeholder: {
    type: String,
    default: 'Cerca operatore per nome, email o ID...'
  },
  /** Millisecondi di debounce per le chiamate API */
  debounceMs: {
    type: Number,
    default: 250
  }
});

const emit = defineEmits(['update:modelValue', 'change']);

const containerRef = ref(null);
const searchQuery = ref('');
const results = ref([]);
const loading = ref(false);
const isOpen = ref(false);
const highlightedIndex = ref(-1);

const selectedId = ref(props.modelValue);
const selectedItem = ref(null);

let debounceTimeout = null;

// Sincronizza modelValue in ingresso
watch(() => props.modelValue, (newVal) => {
  selectedId.value = newVal;
  if (!newVal) {
    selectedItem.value = null;
    searchQuery.value = '';
  }
}, { immediate: true });

// Carica i risultati tramite API
const fetchUsers = async (query = '') => {
  loading.value = true;
  try {
    const data = await filterService.searchUsers({
      q: query,
      table: props.table,
      id_field: props.idField,
      label_field: props.labelField,
      limit: 20
    });
    results.value = Array.isArray(data) ? data : [];
    isOpen.value = true;
    highlightedIndex.value = results.value.length > 0 ? 0 : -1;
  } catch (err) {
    console.error('Errore durante la ricerca utenti:', err);
    results.value = [];
  } finally {
    loading.value = false;
  }
};

const handleInput = () => {
  clearTimeout(debounceTimeout);
  debounceTimeout = setTimeout(() => {
    fetchUsers(searchQuery.value);
  }, props.debounceMs);
};

const handleFocus = () => {
  if (!selectedId.value) {
    fetchUsers(searchQuery.value);
  }
};

const handleKeyDown = (e) => {
  if (!isOpen.value || results.value.length === 0) return;

  if (e.key === 'ArrowDown') {
    e.preventDefault();
    highlightedIndex.value = (highlightedIndex.value + 1) % results.value.length;
  } else if (e.key === 'ArrowUp') {
    e.preventDefault();
    highlightedIndex.value = (highlightedIndex.value - 1 + results.value.length) % results.value.length;
  } else if (e.key === 'Enter') {
    e.preventDefault();
    if (highlightedIndex.value >= 0 && highlightedIndex.value < results.value.length) {
      selectItem(results.value[highlightedIndex.value]);
    }
  } else if (e.key === 'Escape') {
    isOpen.value = false;
  }
};

/**
 * Seleziona un elemento.
 * IMPORTANTE: Emette ESCLUSIVAMENTE l'ID (numero o stringa), MAI l'oggetto.
 */
const selectItem = (item) => {
  if (!item || item.id === undefined || item.id === null) return;

  const idOnly = item.id;
  selectedId.value = idOnly;
  selectedItem.value = item;
  searchQuery.value = '';
  isOpen.value = false;

  // Emette rigorosamente l'ID
  emit('update:modelValue', idOnly);
  emit('change', idOnly);
};

/**
 * Resetta la selezione.
 */
const resetSelection = () => {
  selectedId.value = null;
  selectedItem.value = null;
  searchQuery.value = '';
  isOpen.value = false;

  emit('update:modelValue', null);
  emit('change', null);
};

const clearSearch = () => {
  searchQuery.value = '';
  fetchUsers('');
};

const getInitials = (text) => {
  if (!text) return '?';
  const parts = String(text).trim().split(/\s+/);
  if (parts.length >= 2) {
    return (parts[0][0] + parts[1][0]).toUpperCase();
  }
  return String(text).slice(0, 2).toUpperCase();
};

// Chiudi dropdown al click esterno
const handleClickOutside = (e) => {
  if (containerRef.value && !containerRef.value.contains(e.target)) {
    isOpen.value = false;
  }
};

onMounted(() => {
  document.addEventListener('click', handleClickOutside);
});

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside);
  clearTimeout(debounceTimeout);
});
</script>
