<template>
  <div class="bg-white p-6 sm:p-7 rounded-2xl border border-slate-200 shadow-sm space-y-4">
    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
      <div>
        <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Aggiungi Parametro di Competenza</h3>
        <p class="text-xs text-slate-500 mt-0.5">Assegna un nuovo vincolo di visibilità all'operatore selezionato.</p>
      </div>
      <span class="text-xs bg-indigo-50 text-indigo-700 font-semibold px-2.5 py-1 rounded-lg border border-indigo-100">
        Nuovo Filtro
      </span>
    </div>

    <form @submit.prevent="handleSubmit" class="space-y-4">
      <div class="grid grid-cols-1 sm:grid-cols-12 gap-4 items-start">
        
        <!-- 1. Cosa vuoi filtrare (Definizione / Scope) -->
        <div class="sm:col-span-5 space-y-1">
          <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide">
            1. Criterio di Competenza
          </label>
          <select 
            v-model="selectedId" 
            @change="handleTypeChange" 
            class="w-full border border-slate-300 rounded-xl p-2.5 bg-slate-50/70 text-xs sm:text-sm font-medium focus:ring-2 focus:ring-indigo-500 focus:bg-white transition" 
            required
          >
            <option value="">-- Seleziona Ambito di Filtro --</option>
            <option v-for="def in definitions" :key="def.id" :value="def.id">
              {{ formatClassName(def.scope_filter) }} (per {{ formatClassName(def.model_class) }})
            </option>
          </select>
          <p class="text-[10px] text-slate-400">Determina su quale entità viene applicata la restrizione.</p>
        </div>

        <!-- 2. Valore del Filtro (ID) -->
        <div class="sm:col-span-3 space-y-1">
          <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide">
            2. ID Valore / Codice
          </label>
          <input 
            type="text" 
            v-model="form.filterable_id" 
            placeholder="es. 10 o UFF-01" 
            class="w-full border border-slate-300 rounded-xl p-2.5 text-xs sm:text-sm font-mono bg-white focus:ring-2 focus:ring-indigo-500 transition" 
            required 
          />
          <p class="text-[10px] text-slate-400">L'ID o codice dell'elemento autorizzato.</p>
        </div>

        <!-- 3. Gruppo Logico (AND / OR) -->
        <div class="sm:col-span-2 space-y-1">
          <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide">
            3. Gruppo
          </label>
          <input 
            type="number" 
            v-model.number="form.group" 
            min="1" 
            class="w-full border border-slate-300 rounded-xl p-2.5 text-xs sm:text-sm font-mono bg-white focus:ring-2 focus:ring-indigo-500 transition" 
            required 
          />
          <p class="text-[10px] text-slate-400">Stesso gruppo = AND, diverso = OR.</p>
        </div>

        <!-- 4. Pulsante Salva -->
        <div class="sm:col-span-2 sm:pt-6">
          <button 
            type="submit" 
            class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl p-2.5 text-xs sm:text-sm shadow transition duration-150 flex items-center justify-center gap-1.5"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>Aggiungi</span>
          </button>
        </div>

      </div>

      <!-- OPZIONE AGGIUNTIVA: INCLUDI FIGLI (ALBERO) -->
      <div class="pt-3 border-t border-slate-100 space-y-3">
        <div class="flex items-center justify-between">
          <label class="flex items-center gap-2.5 cursor-pointer select-none">
            <input 
              type="checkbox" 
              v-model="form.include_children" 
              class="w-4 h-4 text-indigo-600 rounded border-slate-300 focus:ring-indigo-500" 
            />
            <span class="text-xs font-semibold text-slate-700">
              Includi automaticamente tutti i sotto-elementi (gerarchia ad albero ricorsiva)
            </span>
          </label>
          <span class="text-[11px] text-slate-400 hidden sm:inline">
            Risolve i nodi discendenti dell'albero
          </span>
        </div>

        <!-- CAMPO CHE APPARE QUANDO LA CHECKBOX È SELEZIONATA -->
        <div v-if="form.include_children" class="pl-6 pt-1 space-y-1 bg-indigo-50/50 p-3 rounded-xl border border-indigo-100 transition-all duration-200">
          <div class="flex items-center justify-between">
            <label class="block text-xs font-bold text-slate-800">
              Nome della colonna genitore (Opzionale)
            </label>
            <span class="text-[10px] font-bold text-indigo-600 bg-white px-2 py-0.5 rounded border border-indigo-200">
              Default: padre_id o rilevata da DB
            </span>
          </div>
          <p class="text-[11px] text-slate-500">
            Se la colonna che collega i nodi al genitore non si chiama <code>padre_id</code>, scrivi qui il nome esatto (es. <code>parent_id</code>, <code>id_padre</code>).
          </p>
          <input 
            type="text" 
            v-model="form.parent_column" 
            placeholder="Lascia vuoto per usare il default ('padre_id')" 
            class="w-full border border-slate-300 rounded-lg p-2 text-xs font-mono font-bold text-slate-900 bg-white focus:ring-2 focus:ring-indigo-500 transition" 
          />
        </div>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, reactive, watch } from 'vue';
import { filterService } from '../../services/filterService';

const props = defineProps({
  definitions: {
    type: Array,
    default: () => []
  },
  selectedUserId: { 
    type: [Number, String], 
    required: true 
  }
});

const emit = defineEmits(['filter-created']);

const selectedId = ref('');
const form = reactive({ 
  user_id: props.selectedUserId, 
  filterable_type: '', 
  filterable_id: '',  
  include_children: false,
  parent_column: '',
  group: 1 
});

// Aggiorna l'user_id non appena cambia la prop
watch(() => props.selectedUserId, (newId) => {
  form.user_id = newId;
}, { immediate: true });

const formatClassName = (fullClass) => {
  if (!fullClass) return '';
  return fullClass.split('\\').pop();
};

const handleTypeChange = () => {
  const def = props.definitions.find(d => d.id == selectedId.value);
  form.filterable_type = def ? def.scope_filter : '';
};

const handleSubmit = async () => {
  try {
    form.user_id = props.selectedUserId;

    await filterService.createUserFilter(form);
    
    // Reset del form
    form.filterable_id = '';
    form.include_children = false;
    selectedId.value = '';
    emit('filter-created');
  } catch (err) {
    alert("Errore durante il salvataggio del filtro.");
  }
};
</script>
