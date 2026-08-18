<!-- MODALE CLONAZIONE COMPETENZE -->
<div 
  v-if="isCloneModalOpen" 
  class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
>
  <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl border border-slate-200 space-y-6 animate-in fade-in zoom-in-95 duration-200">
    <div class="flex items-center justify-between border-b border-slate-100 pb-4">
      <div class="flex items-center gap-2.5">
        <div class="p-2 bg-indigo-50 text-indigo-600 rounded-xl">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" /></svg>
        </div>
        <h3 class="text-base font-extrabold text-slate-900">Clona Competenze Operatore</h3>
      </div>
      <button @click="isCloneModalOpen = false" class="text-slate-400 hover:text-slate-600 cursor-pointer">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
      </button>
    </div>

    <div class="space-y-4">
      <p class="text-xs text-slate-600 leading-relaxed">
        Copia tutti i filtri di visibilità di 
        <strong class="text-indigo-600">@{{ selectedUser ? (selectedUser.label || selectedUser.name || 'Utente #' + selectedUser.id) : '' }}</strong> 
        su altri operatori.
      </p>

      <!-- SELEZIONE DESTINATARI -->
      <div class="space-y-2">
        <label class="block text-xs font-bold text-slate-700">Seleziona Operatori Destinatari</label>
        <div class="max-h-48 overflow-y-auto border border-slate-200 rounded-xl p-2 divide-y divide-slate-100">
          <div 
            v-for="u in availableTargetUsers" 
            :key="u.id"
            class="py-2 px-2 flex items-center justify-between hover:bg-slate-50 rounded-lg"
          >
            <label :for="'target_u_' + u.id" class="text-xs font-semibold text-slate-800 cursor-pointer flex-1">
              @{{ u.label || u.name || 'Utente #' + u.id }}
              <span v-if="u.email || u.sublabel" class="text-slate-500 font-normal ml-1">(@{{ u.email || u.sublabel }})</span>
              <span class="text-slate-400 font-normal ml-1">[ID: @{{ u.id }}]</span>
            </label>
            <input 
              :id="'target_u_' + u.id"
              type="checkbox" 
              :value="u.id" 
              v-model="cloneTargetUserIds"
              class="w-4 h-4 text-indigo-600 rounded border-slate-300 focus:ring-indigo-500 cursor-pointer"
            >
          </div>
        </div>
      </div>

      <!-- MODALITÀ (REPLACE / MERGE) -->
      <div class="grid grid-cols-2 gap-3">
        <div 
          @click="cloneMode = 'replace'"
          :class="[cloneMode === 'replace' ? 'border-indigo-600 bg-indigo-50/70 text-indigo-950 font-bold' : 'border-slate-200 text-slate-600']"
          class="p-3 border-2 rounded-xl cursor-pointer text-xs flex flex-col justify-between space-y-1 transition"
        >
          <span>Sovrascrivi (Replace)</span>
          <span class="text-[10px] text-slate-500 font-normal">Sostituisce i vecchi filtri</span>
        </div>
        <div 
          @click="cloneMode = 'merge'"
          :class="[cloneMode === 'merge' ? 'border-indigo-600 bg-indigo-50/70 text-indigo-950 font-bold' : 'border-slate-200 text-slate-600']"
          class="p-3 border-2 rounded-xl cursor-pointer text-xs flex flex-col justify-between space-y-1 transition"
        >
          <span>Unisci (Merge)</span>
          <span class="text-[10px] text-slate-500 font-normal">Aggiunge ai filtri esistenti</span>
        </div>
      </div>

      <div class="flex items-center justify-end gap-3 pt-2">
        <button 
          type="button" 
          @click="isCloneModalOpen = false" 
          class="px-4 py-2.5 text-xs font-bold text-slate-600 hover:text-slate-900 rounded-xl cursor-pointer"
        >
          Annulla
        </button>
        <button 
          type="button" 
          @click="executeClone" 
          :disabled="cloneTargetUserIds.length === 0 || isCloning"
          class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-extrabold rounded-xl shadow-xs transition disabled:opacity-50 cursor-pointer"
        >
          @{{ isCloning ? 'Clonazione in corso...' : 'Conferma Clonazione' }}
        </button>
      </div>
    </div>
  </div>
</div>
