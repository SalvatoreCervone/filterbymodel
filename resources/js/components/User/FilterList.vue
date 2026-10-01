<template>
  <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="p-6 border-b border-slate-100 flex items-center justify-between bg-slate-50/60">
      <div>
        <h3 class="text-base font-bold text-slate-900">Filtri di Competenza Attivi</h3>
        <p class="text-xs text-slate-500 mt-0.5">Elenco delle regole e perimetri assegnati a questo utente.</p>
      </div>
      <span v-if="!loading" class="text-xs font-semibold px-2.5 py-1 bg-slate-100 text-slate-700 rounded-lg border border-slate-200">
        {{ filters.length }} Filtri
      </span>
    </div>

    <div v-if="loading" class="p-12 text-center text-sm text-slate-500 flex flex-col items-center justify-center gap-3">
      <svg class="animate-spin h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
      </svg>
      <span>Caricamento filtri in corso...</span>
    </div>

    <div v-else-if="filters.length === 0" class="p-10 text-center text-slate-400 text-sm space-y-1.5">
      <p class="font-medium text-slate-500">Nessun filtro attivo per questo utente.</p>
      <p class="text-xs text-slate-400">L'operatore avrà accesso completo o nessun perimetro ristretto in base alle regole globali.</p>
    </div>

    <div v-else class="overflow-x-auto">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="bg-slate-50/80 border-b border-slate-200 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
            <th class="px-6 py-3.5">Criterio (Tipo Oggetto)</th>
            <th class="px-6 py-3.5">Ambito Validità</th>
            <th class="px-6 py-3.5">ID / Valore Autorizzato</th>
            <th class="px-6 py-3.5">Gruppo Logico</th>
            <th class="px-6 py-3.5">Gerarchia Albero</th>
            <th class="px-6 py-3.5 text-right">Azioni</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
          <tr v-for="filter in filters" :key="filter.id" class="hover:bg-slate-50/70 transition">
            <td class="px-6 py-4 whitespace-nowrap">
              <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-100">
                {{ formatClassName(filter.filterable_type) }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span 
                v-if="filter.target_model" 
                class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200"
              >
                Solo per {{ formatClassName(filter.target_model) }}
              </span>
              <span 
                v-else 
                class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-600"
              >
                Tutte le schede (Globale)
              </span>
            </td>
            <td class="px-6 py-4 font-mono font-bold text-slate-900">
              {{ filter.filterable_id }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-700 border border-slate-200">
                Gruppo {{ filter.group }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div v-if="filter.include_children" class="flex flex-col items-start gap-1">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                  ✓ Inclusi Figli
                </span>
                <span class="text-[10px] font-mono text-slate-600 bg-slate-100 px-1.5 py-0.5 rounded border border-slate-200">
                  Colonna: <b class="text-indigo-700">{{ filter.parent_column || 'padre_id (Default)' }}</b>
                </span>
                <span v-if="filter.key_column" class="text-[10px] font-mono text-slate-600 bg-slate-100 px-1.5 py-0.5 rounded border border-slate-200">
                  Chiave: <b class="text-purple-700">{{ filter.key_column }}</b>
                </span>
              </div>
              <span 
                v-else
                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-400"
              >
                Solo Questo Nodo
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right">
              <div class="flex items-center justify-end gap-1.5">
                <button 
                  type="button"
                  @click="$emit('filter-edit', filter)"
                  :class="[
                    'text-xs font-semibold px-2.5 py-1.5 rounded-lg transition flex items-center gap-1 cursor-pointer',
                    editingFilterId === filter.id 
                      ? 'bg-indigo-100 text-indigo-800 ring-1 ring-indigo-400' 
                      : 'text-indigo-600 hover:text-indigo-800 bg-indigo-50 hover:bg-indigo-100'
                  ]"
                  title="Modifica parametri competenza"
                >
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                  </svg>
                  <span>{{ editingFilterId === filter.id ? 'In Modifica' : 'Modifica' }}</span>
                </button>
                <button 
                  type="button"
                  @click="deleteFilter(filter.id)"
                  class="text-xs text-rose-600 hover:text-rose-700 font-medium bg-rose-50 hover:bg-rose-100 px-3 py-1.5 rounded-lg transition cursor-pointer"
                  title="Rimuovi competenza"
                >
                  Rimuovi
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { filterService } from '../../services/filterService'; 

const props = defineProps({
  filters: { type: Array, required: true },
  loading: { type: Boolean, default: false },
  editingFilterId: { type: [Number, String], default: null }
});

const emit = defineEmits(['filter-deleted', 'filter-edit']);

const deleteFilter = async (id) => {
  if (confirm("Vuoi davvero rimuovere questo parametro di filtro?")) {
    try {
      await filterService.deleteUserFilter(id);
      emit('filter-deleted', id);
    } catch (err) {
      console.error("Errore nella cancellazione del filtro", err);
    }
  }
};

const formatClassName = (fullClass) => {
  if (!fullClass) return '';
  return fullClass.split('\\').pop();
};
</script>
