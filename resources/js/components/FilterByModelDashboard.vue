<script setup>
import { ref } from 'vue';
import FilterDefinitionManager from './FilterDefinitionManager.vue';
import FilterManager from './User/FilterManager.vue';

const props = defineProps({
  userTable: {
    type: String,
    default: 'users'
  },
  userIdField: {
    type: String,
    default: 'id'
  },
  userLabelField: {
    type: String,
    default: 'name'
  },
  initialTab: {
    type: String,
    default: 'definitions' // 'definitions' | 'users'
  }
});

const activeTab = ref(props.initialTab);
</script>

<template>
  <div class="p-6 max-w-7xl mx-auto space-y-6">
    <!-- BARRA DI NAVIGAZIONE A SCHEDE -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
      <div class="flex items-center gap-3">
        <div class="p-2.5 bg-indigo-600 text-white rounded-xl shadow-xs">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
          </svg>
        </div>
        <div>
          <h2 class="text-base font-extrabold text-slate-900 tracking-tight">FilterByModel Admin</h2>
          <p class="text-xs text-slate-500 font-medium">Gestione perimetri di sicurezza e visibilità multi-tenant a livello di modello.</p>
        </div>
      </div>

      <div class="flex items-center gap-1.5 bg-slate-100 p-1 rounded-xl border border-slate-200/80">
        <button 
          type="button"
          @click="activeTab = 'definitions'" 
          :class="[activeTab === 'definitions' ? 'bg-white text-indigo-700 shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900 font-medium']"
          class="px-4 py-2 rounded-lg text-xs transition-all flex items-center gap-2 cursor-pointer"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
          Regole Modelli (Admin)
        </button>

        <button 
          type="button"
          @click="activeTab = 'users'" 
          :class="[activeTab === 'users' ? 'bg-white text-indigo-700 shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900 font-medium']"
          class="px-4 py-2 rounded-lg text-xs transition-all flex items-center gap-2 cursor-pointer"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
          Competenze & Operatori
        </button>
      </div>
    </div>

    <!-- VISTA 1: GESTIONE DEFINIZIONI -->
    <FilterDefinitionManager v-if="activeTab === 'definitions'" />

    <!-- VISTA 2: GESTIONE COMPETENZE OPERATORI -->
    <FilterManager 
      v-else-if="activeTab === 'users'"
      :user-table="userTable"
      :user-id-field="userIdField"
      :user-label-field="userLabelField"
    />
  </div>
</template>
