<template>
  <div v-if="isOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 overflow-y-auto">
    <!-- BACKDROP SFOCATO -->
    <div 
      class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity animate-in fade-in duration-200"
      @click="close"
    ></div>

    <!-- MODAL CONTAINER -->
    <div class="relative bg-white rounded-2xl border border-slate-200 shadow-2xl w-full max-w-xl overflow-hidden z-10 animate-in zoom-in-95 duration-200">
      
      <!-- HEADER -->
      <div class="p-5 sm:p-6 bg-gradient-to-r from-indigo-900 via-indigo-800 to-slate-900 text-white flex items-start justify-between gap-4">
        <div class="flex items-center gap-3">
          <div class="p-2.5 bg-white/10 rounded-xl border border-white/20">
            <svg class="w-6 h-6 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" />
            </svg>
          </div>
          <div>
            <h3 class="text-lg font-extrabold tracking-tight">Copia & Clona Competenze</h3>
            <p class="text-xs text-indigo-200 mt-0.5">
              Duplica i perimetri dell'operatore sorgente su uno o più colleghi.
            </p>
          </div>
        </div>

        <button 
          type="button" 
          @click="close" 
          class="text-indigo-200 hover:text-white p-1 rounded-lg hover:bg-white/10 transition-colors"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- BODY -->
      <div class="p-6 space-y-6">
        
        <!-- SORGENTE INFO -->
        <div class="flex items-center justify-between p-3.5 bg-slate-50 border border-slate-200 rounded-xl text-xs">
          <div class="flex items-center gap-2 text-slate-700">
            <span class="font-bold uppercase tracking-wider text-slate-400">Sorgente:</span>
            <span class="font-bold text-slate-900">Operatore ID: {{ sourceUserId }}</span>
          </div>
          <span class="px-2.5 py-1 rounded-full font-bold bg-indigo-50 text-indigo-700 border border-indigo-100">
            {{ sourceFilters.length }} {{ sourceFilters.length === 1 ? 'regola' : 'regole' }} da clonare
          </span>
        </div>

        <!-- RICERCA E SELEZIONE DESTINATARI -->
        <div class="space-y-3">
          <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
            Seleziona Operatori di Destinazione
          </label>

          <!-- Autocomplete per aggiungere operatori -->
          <UserAutocomplete
            :table="table"
            :id-field="idField"
            :label-field="labelField"
            placeholder="Cerca e aggiungi un operatore alla lista..."
            @update:model-value="addTargetUser"
            ref="autocompleteRef"
          />

          <!-- LISTA OPERATORI AGGIUNTI (TAGS / BADGES) -->
          <div v-if="targetUsers.length > 0" class="space-y-2">
            <div class="flex items-center justify-between text-xs text-slate-500 font-semibold">
              <span>Destinatari selezionati ({{ targetUsers.length }}):</span>
              <button 
                type="button" 
                @click="targetUsers = []" 
                class="text-red-500 hover:text-red-700 text-[11px] underline"
              >
                Rimuovi tutti
              </button>
            </div>

            <div class="flex flex-wrap gap-2 max-h-32 overflow-y-auto p-1.5 bg-slate-50 border border-slate-200 rounded-xl">
              <div 
                v-for="target in targetUsers" 
                :key="target"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white border border-indigo-200 text-indigo-800 text-xs font-semibold shadow-2xs animate-in zoom-in-95 duration-100"
              >
                <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                <span>ID: {{ target }}</span>
                <button 
                  type="button" 
                  @click="removeTargetUser(target)"
                  class="ml-1 text-slate-400 hover:text-red-600 rounded-full p-0.5 hover:bg-slate-100 transition-colors"
                >
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>
            </div>
          </div>

          <div v-else class="text-[11px] text-slate-400 italic">
            Nessun operatore di destinazione selezionato. Usa la barra di ricerca sopra per aggiungerne uno o più.
          </div>
        </div>

        <!-- MODALITÀ DI COPIA (REPLACE vs MERGE) -->
        <div class="space-y-2 pt-2 border-t border-slate-100">
          <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
            Modalità di Assegnazione
          </label>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <!-- Opzione Sostituisci -->
            <label 
              :class="[
                'p-3.5 rounded-xl border cursor-pointer transition-all flex flex-col justify-between text-xs space-y-1',
                mode === 'replace' ? 'bg-indigo-50/70 border-indigo-500 ring-2 ring-indigo-200' : 'bg-white border-slate-200 hover:border-slate-300'
              ]"
            >
              <div class="flex items-center gap-2">
                <input 
                  type="radio" 
                  v-model="mode" 
                  value="replace" 
                  class="text-indigo-600 focus:ring-indigo-500 h-4 w-4"
                />
                <span class="font-bold text-slate-800">Sostituisci (Reset)</span>
              </div>
              <p class="text-[11px] text-slate-500 pl-6">
                Cancella i filtri preesistenti dei destinatari e applica solo quelli copiati.
              </p>
            </label>

            <!-- Opzione Unisci -->
            <label 
              :class="[
                'p-3.5 rounded-xl border cursor-pointer transition-all flex flex-col justify-between text-xs space-y-1',
                mode === 'merge' ? 'bg-indigo-50/70 border-indigo-500 ring-2 ring-indigo-200' : 'bg-white border-slate-200 hover:border-slate-300'
              ]"
            >
              <div class="flex items-center gap-2">
                <input 
                  type="radio" 
                  v-model="mode" 
                  value="merge" 
                  class="text-indigo-600 focus:ring-indigo-500 h-4 w-4"
                />
                <span class="font-bold text-slate-800">Unisci (Merge)</span>
              </div>
              <p class="text-[11px] text-slate-500 pl-6">
                Conserva i filtri già presenti e aggiunge solo le nuove regole mancanti.
              </p>
            </label>
          </div>
        </div>

        <!-- MESSAGGIO DI ERRORE O FEEDBACK -->
        <div v-if="errorMessage" class="p-3 bg-red-50 border border-red-200 rounded-xl text-xs text-red-700 font-medium">
          {{ errorMessage }}
        </div>

      </div>

      <!-- FOOTER -->
      <div class="p-4 sm:p-5 bg-slate-50 border-t border-slate-200 flex items-center justify-end gap-3">
        <button
          type="button"
          @click="close"
          class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-200 transition-colors"
        >
          Annulla
        </button>

        <button
          type="button"
          @click="handleCopy"
          :disabled="targetUsers.length === 0 || loading"
          :class="[
            'px-5 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 shadow-sm',
            targetUsers.length > 0 && !loading 
              ? 'bg-indigo-600 hover:bg-indigo-700 text-white shadow-indigo-200 hover:shadow-indigo-300' 
              : 'bg-slate-200 text-slate-400 cursor-not-allowed'
          ]"
        >
          <svg v-if="loading" class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" />
          </svg>
          <span>Conferma e Clona ({{ targetUsers.length }})</span>
        </button>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import UserAutocomplete from './UserAutocomplete.vue';
import { filterService } from '../../services/filterService';

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false
  },
  sourceUserId: {
    type: [Number, String],
    required: true
  },
  sourceFilters: {
    type: Array,
    default: () => []
  },
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

const emit = defineEmits(['close', 'copied']);

const targetUsers = ref([]);
const mode = ref('replace'); // 'replace' o 'merge'
const loading = ref(false);
const errorMessage = ref('');
const autocompleteRef = ref(null);

const addTargetUser = (userId) => {
  if (!userId) return;

  if (String(userId) === String(props.sourceUserId)) {
    errorMessage.value = "Non puoi selezionare lo stesso operatore sorgente come destinatario.";
    return;
  }

  if (!targetUsers.value.includes(userId)) {
    targetUsers.value.push(userId);
    errorMessage.value = '';
  }

  // Reset del campo di ricerca dopo l'aggiunta
  if (autocompleteRef.value && typeof autocompleteRef.value.resetSelection === 'function') {
    autocompleteRef.value.resetSelection();
  }
};

const removeTargetUser = (userId) => {
  targetUsers.value = targetUsers.value.filter(id => id !== userId);
};

const close = () => {
  targetUsers.value = [];
  errorMessage.value = '';
  loading.value = false;
  emit('close');
};

const handleCopy = async () => {
  if (targetUsers.value.length === 0) return;

  loading.value = true;
  errorMessage.value = '';

  try {
    const result = await filterService.copyUserFilters({
      source_user_id: props.sourceUserId,
      target_user_ids: targetUsers.value,
      mode: mode.value
    });

    emit('copied', result);
    close();
  } catch (err) {
    console.error('Errore durante la clonazione dei filtri:', err);
    errorMessage.value = err.response?.data?.message || 'Si è verificato un errore durante la clonazione.';
  } finally {
    loading.value = false;
  }
};
</script>
