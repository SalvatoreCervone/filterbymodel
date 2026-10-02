<template>
  <div class="bg-white p-6 sm:p-7 rounded-2xl border border-slate-200 shadow-sm space-y-4" id="user-filter-form">
    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
      <div>
        <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
          <span>{{ editingFilter ? 'Modifica Parametro di Competenza #' + editingFilter.id : 'Aggiungi Parametro di Competenza' }}</span>
          <span v-if="editingFilter" class="text-[10px] bg-amber-100 text-amber-800 font-bold px-2 py-0.5 rounded-full border border-amber-200">
            In Modifica
          </span>
        </h3>
        <p class="text-xs text-slate-500 mt-0.5">
          {{ editingFilter ? 'Modifica i parametri nei campi sottostanti e salva per aggiornare la regola.' : 'Assegna un nuovo vincolo di visibilità all\'operatore selezionato.' }}
        </p>
      </div>
      <div class="flex items-center gap-2">
        <button 
          v-if="editingFilter"
          type="button" 
          @click="handleCancel"
          class="text-xs text-slate-600 hover:text-slate-900 font-semibold px-2.5 py-1 rounded-lg border border-slate-200 hover:bg-slate-50 transition cursor-pointer"
        >
          Annulla
        </button>
        <span v-else class="text-xs bg-indigo-50 text-indigo-700 font-semibold px-2.5 py-1 rounded-lg border border-indigo-100">
          Nuovo Filtro
        </span>
      </div>
    </div>

    <form @submit.prevent="handleSubmit" class="space-y-4">
      <div class="grid grid-cols-1 sm:grid-cols-12 gap-4 items-start">
        
        <!-- 1. Cosa vuoi filtrare (Definizione / Scope) -->
        <div class="sm:col-span-4 space-y-1">
          <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide">
            1. Criterio di Competenza
          </label>
          <select 
            v-model="form.filterable_type" 
            @change="onScopeChange"
            class="w-full border border-slate-300 rounded-xl p-2.5 bg-slate-50/70 text-xs sm:text-sm font-medium focus:ring-2 focus:ring-indigo-500 focus:bg-white transition" 
            required
          >
            <option value="">-- Seleziona Criterio --</option>
            <option v-for="crit in availableCriteria" :key="crit.scope_filter" :value="crit.scope_filter">
              {{ crit.name }}{{ crit.target_models.length ? ' (protegge: ' + crit.target_models.join(', ') + ')' : '' }}
            </option>
            <option v-if="form.filterable_type && !availableCriteria.some(c => c.scope_filter === form.filterable_type)" :value="form.filterable_type">
              {{ formatClassName(form.filterable_type) }}
            </option>
          </select>
          <p class="text-[10px] text-slate-400">Determina su quale entità viene applicata la restrizione.</p>
        </div>

        <!-- 1.bis Ambito di Validità (Globale o Modello Specifico) -->
        <div class="sm:col-span-3 space-y-1">
          <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide">
            Ambito Validità
          </label>
          <select 
            v-model="form.target_model" 
            class="w-full border border-slate-300 rounded-xl p-2.5 bg-slate-50/70 text-xs sm:text-sm font-medium focus:ring-2 focus:ring-indigo-500 focus:bg-white transition" 
          >
            <option value="">Tutte le schede (Globale)</option>
            <option v-for="m in currentScopeTargetModels" :key="m.class" :value="m.class">
              Solo per {{ m.name }}
            </option>
          </select>
          <p class="text-[10px] text-slate-400">Globale o per scheda specifica.</p>
        </div>

        <!-- 2. Valore del Filtro (ID) -->
        <div class="sm:col-span-2 space-y-1">
          <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide">
            2. ID Valore
          </label>
          <input 
            type="text" 
            v-model="form.filterable_id" 
            placeholder="es. 10" 
            class="w-full border border-slate-300 rounded-xl p-2.5 text-xs sm:text-sm font-mono bg-white focus:ring-2 focus:ring-indigo-500 transition" 
            required 
          />
        </div>

        <!-- 3. Gruppo Logico (AND / OR) -->
        <div class="sm:col-span-1 space-y-1">
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
        </div>

        <!-- 4. Pulsante Salva / Aggiorna -->
        <div class="sm:col-span-2 sm:pt-6">
          <div v-if="editingFilter" class="flex items-center gap-1.5">
            <button 
              type="submit" 
              class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-xl p-2.5 text-xs sm:text-sm shadow transition duration-150 flex items-center justify-center gap-1 cursor-pointer"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
              <span>Salva</span>
            </button>
            <button 
              type="button" 
              @click="handleCancel"
              class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl p-2.5 text-xs sm:text-sm transition cursor-pointer"
              title="Annulla modifica"
            >
              ✕
            </button>
          </div>
          <button 
            v-else
            type="submit" 
            class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl p-2.5 text-xs sm:text-sm shadow transition duration-150 flex items-center justify-center gap-1.5 cursor-pointer"
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
        <div v-if="form.include_children" class="pl-6 pt-1 space-y-2 bg-indigo-50/50 p-3 rounded-xl border border-indigo-100 transition-all duration-200">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div>
              <div class="flex items-center justify-between mb-1">
                <label class="block text-xs font-bold text-slate-800">
                  Colonna Genitore
                </label>
                <span class="text-[10px] font-bold text-indigo-600 bg-white px-1.5 py-0.2 rounded border border-indigo-200">
                  Default: padre_id
                </span>
              </div>
              <input 
                type="text" 
                v-model="form.parent_column" 
                placeholder="es. padre_id" 
                class="w-full border border-slate-300 rounded-lg p-2 text-xs font-mono font-bold text-slate-900 bg-white focus:ring-2 focus:ring-indigo-500 transition" 
              />
            </div>
            <div>
              <div class="flex items-center justify-between mb-1">
                <label class="block text-xs font-bold text-slate-800">
                  Chiave Collegata per Sviluppo Padre-Figlio
                </label>
                <span class="text-[10px] font-bold text-purple-600 bg-white px-1.5 py-0.2 rounded border border-purple-200">
                  Default: PK modello
                </span>
              </div>
              <input 
                type="text" 
                v-model="form.key_column" 
                placeholder="es. id, codice, uuid" 
                class="w-full border border-slate-300 rounded-lg p-2 text-xs font-mono font-bold text-slate-900 bg-white focus:ring-2 focus:ring-indigo-500 transition" 
              />
            </div>
          </div>
        </div>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, reactive, watch, computed } from 'vue';
import { filterService } from '../../services/filterService';

const props = defineProps({
  definitions: {
    type: Array,
    default: () => []
  },
  selectedUserId: { 
    type: [Number, String], 
    required: true 
  },
  editingFilter: {
    type: Object,
    default: null
  }
});

const emit = defineEmits(['filter-created', 'filter-updated', 'cancel-edit']);

const form = reactive({ 
  user_id: props.selectedUserId, 
  filterable_type: '', 
  target_model: '',
  filterable_id: '',  
  include_children: false,
  parent_column: '',
  key_column: '',
  group: 1 
});

const currentScopeTargetModels = computed(() => {
  if (!form.filterable_type) return [];
  const list = [];
  props.definitions.forEach(def => {
    if (def.scope_filter === form.filterable_type) {
      list.push({
        class: def.model_class,
        name: formatClassName(def.model_class)
      });
    }
  });
  return list;
});

const availableCriteria = computed(() => {
  const map = {};
  props.definitions.forEach(def => {
    if (!map[def.scope_filter]) {
      map[def.scope_filter] = {
        scope_filter: def.scope_filter,
        name: formatClassName(def.scope_filter),
        target_models: [],
        parent_column: def.parent_column || null,
        key_column: def.key_column || null
      };
    }
    const modelName = formatClassName(def.model_class);
    if (!map[def.scope_filter].target_models.includes(modelName)) {
      map[def.scope_filter].target_models.push(modelName);
    }
    if (def.parent_column && !map[def.scope_filter].parent_column) {
      map[def.scope_filter].parent_column = def.parent_column;
    }
    if (def.key_column && !map[def.scope_filter].key_column) {
      map[def.scope_filter].key_column = def.key_column;
    }
  });

  // Salvaguardia: se il filtro in modifica ha un criterio non presente nelle definizioni attive
  if (props.editingFilter && props.editingFilter.filterable_type && !map[props.editingFilter.filterable_type]) {
    map[props.editingFilter.filterable_type] = {
      scope_filter: props.editingFilter.filterable_type,
      name: formatClassName(props.editingFilter.filterable_type),
      target_models: [],
      parent_column: props.editingFilter.parent_column || null,
      key_column: props.editingFilter.key_column || null
    };
  }

  return Object.values(map);
});

const resetForm = () => {
  form.filterable_type = '';
  form.target_model = '';
  form.filterable_id = '';
  form.group = 1;
  form.include_children = false;
  form.parent_column = '';
  form.key_column = '';
};

const onScopeChange = () => {
  if (!currentScopeTargetModels.value.some(m => m.class === form.target_model)) {
    form.target_model = '';
  }
  const crit = availableCriteria.value.find(c => c.scope_filter === form.filterable_type);
  if (crit && crit.parent_column && !form.parent_column) {
    form.parent_column = crit.parent_column;
  }
  if (crit && crit.key_column && !form.key_column) {
    form.key_column = crit.key_column;
  }
};

const handleCancel = () => {
  resetForm();
  emit('cancel-edit');
};

// Aggiorna l'user_id non appena cambia la prop
watch(() => props.selectedUserId, (newId) => {
  form.user_id = newId;
  resetForm();
}, { immediate: true });

// Popola il form quando viene passato un filtro da modificare
watch(() => props.editingFilter, (newFilter) => {
  if (newFilter) {
    form.filterable_type = newFilter.filterable_type || '';
    form.target_model = newFilter.target_model || '';
    form.filterable_id = newFilter.filterable_id !== undefined && newFilter.filterable_id !== null ? String(newFilter.filterable_id) : '';
    form.group = newFilter.group !== undefined ? Number(newFilter.group) : 1;
    form.include_children = Boolean(newFilter.include_children);
    form.parent_column = newFilter.parent_column || '';
    form.key_column = newFilter.key_column || '';
  } else {
    resetForm();
  }
}, { immediate: true });

const formatClassName = (fullClass) => {
  if (!fullClass) return '';
  return fullClass.split('\\').pop();
};

const handleSubmit = async () => {
  try {
    form.user_id = props.selectedUserId;

    if (props.editingFilter) {
      await filterService.updateUserFilter(props.editingFilter.id, form);
      resetForm();
      emit('filter-updated');
    } else {
      await filterService.createUserFilter(form);
      resetForm();
      emit('filter-created');
    }
  } catch (err) {
    const errorMsg = err.response?.data?.message || err.message || "Errore durante il salvataggio del filtro.";
    alert(errorMsg);
  }
};
</script>
