<!-- NOTIFICA GLOBALE TOAST -->
<div 
  v-if="toastMessage" 
  class="fixed bottom-5 right-5 z-50 max-w-md p-4 rounded-2xl shadow-xl border flex items-center justify-between gap-3 animate-in fade-in slide-in-from-bottom-5 duration-300"
  :class="[toastType === 'error' ? 'bg-rose-50 border-rose-200 text-rose-800' : 'bg-emerald-50 border-emerald-200 text-emerald-800']"
>
  <div class="flex items-center gap-2.5 text-xs font-semibold">
    <svg v-if="toastType !== 'error'" class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
    <svg v-else class="w-5 h-5 text-rose-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
    <span>@{{ toastMessage }}</span>
  </div>
  <button @click="toastMessage = ''" class="text-slate-400 hover:text-slate-600 p-1 cursor-pointer">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
  </button>
</div>
