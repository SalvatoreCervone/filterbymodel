<!-- SCRIPT VUE 3 APPLICATION LOGIC -->
<script>
  const { createApp, ref, reactive, computed, onMounted } = Vue;

  createApp({
    setup() {
      const apiPrefix = "{{ $apiPrefix }}";
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

      const currentView = ref('admin');
      const focusedField = ref(null);
      const toastMessage = ref('');
      const toastType = ref('success');
      const isSubmitting = ref(false);
      const isCloning = ref(false);

      const showToast = (msg, type = 'success') => {
        toastMessage.value = msg;
        toastType.value = type;
        setTimeout(() => {
          if (toastMessage.value === msg) toastMessage.value = '';
        }, 4000);
      };

      const apiFetch = async (endpoint, options = {}) => {
        const headers = {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
          ...(options.headers || {})
        };
        const response = await fetch(`${apiPrefix}${endpoint}`, { ...options, headers });
        if (!response.ok) {
          const err = await response.json().catch(() => ({ message: response.statusText }));
          throw new Error(err.message || 'Errore nella richiesta API');
        }
        return response.json();
      };

      // --- STATO ADMIN / DEFINIZIONI ---
      const availableModels = ref([]);
      const definitions = ref([]);

      const form = reactive({
        model_class: '',
        scope_filter: '',
        has_pivot: false,
        pivot_table: '',
        pivot_foreign_key: '',
        target_foreign_key: '',
        filter_key: '',
        parent_column: '',
        additional_where: null
      });

      const formatClassName = (fullClass) => {
        if (!fullClass) return '';
        return fullClass.split('\\').pop();
      };

      const setPivotMode = (isPivot) => {
        form.has_pivot = isPivot;
        autoFillFields();
      };

      const getModelMeta = (modelClass) => {
        return availableModels.value.find(m => m.class === modelClass) || null;
      };

      const autoFillFields = () => {
        const targetModel = getModelMeta(form.model_class);
        const scopeModel = getModelMeta(form.scope_filter);

        const targetSnake = targetModel?.snake_name || formatClassName(form.model_class).toLowerCase();
        const scopeSnake = scopeModel?.snake_name || formatClassName(form.scope_filter).toLowerCase();

        if (scopeModel?.foreign_key) {
          form.filter_key = scopeModel.foreign_key;
        } else if (scopeSnake) {
          form.filter_key = `${scopeSnake}_id`;
        }

        if (form.has_pivot && targetSnake && scopeSnake) {
          const segments = [targetSnake, scopeSnake].sort();
          form.pivot_table = `${segments[0]}_${segments[1]}`;
          form.pivot_foreign_key = targetModel?.foreign_key || `${targetSnake}_id`;
        } else if (!form.has_pivot) {
          form.pivot_table = '';
          form.pivot_foreign_key = '';
          form.target_foreign_key = '';
        }
      };

      const simulatedSql = computed(() => {
        const targetModel = getModelMeta(form.model_class);
        const table = targetModel?.table || (targetModel?.snake_name ? targetModel.snake_name + 's' : (formatClassName(form.model_class).toLowerCase() + 's'));
        const filterCol = form.filter_key || 'criterio_id';
        const targetKey = form.target_foreign_key || targetModel?.primary_key || 'id';

        if (!form.has_pivot) {
          return `SELECT * FROM \`${table}\` WHERE \`${table}\`.\`${filterCol}\` IN (1, 2, 5);`;
        } else {
          const pivot = form.pivot_table || `${table}_criteri`;
          const pivotFk = form.pivot_foreign_key || (targetModel?.foreign_key || `${formatClassName(form.model_class).toLowerCase()}_id`);
          return `SELECT * FROM \`${table}\` WHERE EXISTS (\n  SELECT 1 FROM \`${pivot}\`\n  WHERE \`${pivot}\`.\`${pivotFk}\` = \`${table}\`.\`${targetKey}\`\n  AND \`${pivot}\`.\`${filterCol}\` IN (1, 2, 5)\n);`;
        }
      });

      const loadAvailableModels = async () => {
        try {
          const data = await apiFetch('/available-models');
          availableModels.value = data.data || data;
          if (availableModels.value.length > 0 && !form.model_class) {
            form.model_class = availableModels.value[0].class;
            if (availableModels.value.length > 1) form.scope_filter = availableModels.value[1].class;
            autoFillFields();
          }
        } catch (e) {
          console.error(e);
        }
      };

      const loadDefinitions = async () => {
        try {
          const data = await apiFetch('/filter-definitions');
          definitions.value = data.data || data;
        } catch (e) {
          console.error(e);
        }
      };

      const rawAdditionalWhere = ref('');
      const jsonError = ref(false);

      const setJsonPreset = (key, val) => {
        let current = {};
        try {
          if (rawAdditionalWhere.value) {
            current = JSON.parse(rawAdditionalWhere.value);
          }
        } catch (e) {
          current = {};
        }
        current[key] = val;
        rawAdditionalWhere.value = JSON.stringify(current, null, 2);
        validateJson();
      };

      const validateJson = () => {
        if (!rawAdditionalWhere.value || !rawAdditionalWhere.value.trim()) {
          form.additional_where = null;
          jsonError.value = false;
          return;
        }
        try {
          const parsed = JSON.parse(rawAdditionalWhere.value);
          form.additional_where = parsed;
          jsonError.value = false;
        } catch (e) {
          jsonError.value = true;
        }
      };

      const saveDefinition = async () => {
        validateJson();
        if (jsonError.value) {
          showToast('Il formato JSON dei filtri addizionali non è valido.', 'error');
          return;
        }

        isSubmitting.value = true;
        try {
          await apiFetch('/filter-definitions', {
            method: 'POST',
            body: JSON.stringify({
              model_class: form.model_class,
              scope_filter: form.scope_filter,
              pivot_table: form.has_pivot ? form.pivot_table : null,
              pivot_foreign_key: form.has_pivot ? form.pivot_foreign_key : null,
              target_foreign_key: form.has_pivot && form.target_foreign_key ? form.target_foreign_key : null,
              filter_key: form.filter_key,
              parent_column: form.parent_column ? form.parent_column : null,
              additional_where: form.additional_where
            })
          });
          showToast('Regola di visibilità salvata con successo!');
          await loadDefinitions();
        } catch (e) {
          showToast(e.message, 'error');
        } finally {
          isSubmitting.value = false;
        }
      };

      const deleteDefinition = async (id) => {
        if (!confirm('Vuoi davvero eliminare questa regola di visibilità?')) return;
        try {
          await apiFetch(`/filter-definitions/${id}`, { method: 'DELETE' });
          showToast('Regola eliminata con successo.');
          await loadDefinitions();
        } catch (e) {
          showToast(e.message, 'error');
        }
      };

      // --- STATO COMPETENZE UTENTE ---
      const userSearchQuery = ref('');
      const userSearchResults = ref([]);
      const isUserDropdownOpen = ref(false);
      const selectedUser = ref(null);
      const currentUserFilters = ref([]);
      let searchTimeout = null;

      const userForm = reactive({
        scope_filter: '',
        filterable_id: '',
        group: 1,
        include_children: false,
        parent_column: ''
      });

      const onUserSearchInput = () => {
        clearTimeout(searchTimeout);
        isUserDropdownOpen.value = true;
        searchTimeout = setTimeout(async () => {
          if (!userSearchQuery.value.trim()) {
            userSearchResults.value = [];
            return;
          }
          try {
            const res = await apiFetch(`/search-users?q=${encodeURIComponent(userSearchQuery.value)}`);
            userSearchResults.value = res.data || res;
          } catch (e) {
            console.error(e);
          }
        }, 300);
      };

      const selectUser = async (u) => {
        selectedUser.value = u;
        userSearchQuery.value = u.label || u.name || `Utente #${u.id}`;
        isUserDropdownOpen.value = false;
        await loadUserFilters(u.id);
      };

      const loadUserFilters = async (userId) => {
        try {
          const data = await apiFetch(`/user-filters?user_id=${userId}`);
          currentUserFilters.value = data.data || data;
        } catch (e) {
          console.error(e);
        }
      };

      const onScopeFilterChange = () => {
        const selectedDef = definitions.value.find(d => d.scope_filter === userForm.scope_filter);
        if (selectedDef && selectedDef.parent_column) {
          userForm.parent_column = selectedDef.parent_column;
          userForm.include_children = true;
        }
      };

      const saveUserFilter = async () => {
        if (!selectedUser.value) return;
        if (!userForm.scope_filter || !userForm.filterable_id) {
          showToast('Seleziona il criterio e inserisci l\'ID del valore.', 'error');
          return;
        }

        // Verifica duplicati lato client
        const isDuplicate = currentUserFilters.value.some(f => 
          f.filterable_type === userForm.scope_filter &&
          String(f.filterable_id) === String(userForm.filterable_id) &&
          Number(f.group) === Number(userForm.group || 1)
        );

        if (isDuplicate) {
          showToast(`Questa competenza è già stata assegnata all'operatore per il Gruppo ${userForm.group || 1}.`, 'error');
          return;
        }

        try {
          await apiFetch('/user-filters', {
            method: 'POST',
            body: JSON.stringify({
              user_id: selectedUser.value.id,
              filterable_type: userForm.scope_filter,
              filterable_id: userForm.filterable_id,
              group: userForm.group || 1,
              include_children: userForm.include_children,
              parent_column: userForm.parent_column ? userForm.parent_column : null
            })
          });
          showToast('Competenza assegnata all\'operatore!');
          userForm.filterable_id = '';
          await loadUserFilters(selectedUser.value.id);
        } catch (e) {
          showToast(e.message, 'error');
        }
      };

      const deleteUserFilter = async (id) => {
        if (!confirm('Vuoi rimuovere questo filtro di competenza?')) return;
        try {
          await apiFetch(`/user-filters/${id}`, { method: 'DELETE' });
          showToast('Competenza revocata.');
          if (selectedUser.value) await loadUserFilters(selectedUser.value.id);
        } catch (e) {
          showToast(e.message, 'error');
        }
      };

      // --- STATO CLONAZIONE & RESOCONTO ---
      const isCloneModalOpen = ref(false);
      const cloneTargetUserIds = ref([]);
      const cloneMode = ref('replace');
      const summaryData = ref([]);
      const summaryStatsData = ref({ total: 0, with_filters: 0, without_filters: 0 });
      const summaryStatusFilter = ref('all');
      const summarySearchQuery = ref('');

      const loadSummary = async () => {
        try {
          const res = await apiFetch('/user-filters-summary');
          if (res && res.data) {
            summaryData.value = res.data;
            summaryStatsData.value = {
              total: res.total_users ?? res.data.length,
              with_filters: res.total_with_filters ?? res.data.filter(u => u.has_filters).length,
              without_filters: res.total_without_filters ?? res.data.filter(u => !u.has_filters).length
            };
          } else if (Array.isArray(res)) {
            summaryData.value = res;
            summaryStatsData.value = {
              total: res.length,
              with_filters: res.filter(u => u.has_filters).length,
              without_filters: res.filter(u => !u.has_filters).length
            };
          }
        } catch (e) {
          console.error(e);
        }
      };

      const summaryStats = computed(() => {
        const total = summaryStatsData.value.total || summaryData.value.length;
        const withFilters = summaryStatsData.value.with_filters || summaryData.value.filter(u => u.has_filters).length;
        const withoutFilters = total - withFilters;
        return { total, withFilters, withoutFilters };
      });

      const filteredSummaryList = computed(() => {
        let list = summaryData.value;
        if (summaryStatusFilter.value === 'with_filters') {
          list = list.filter(u => u.has_filters);
        } else if (summaryStatusFilter.value === 'without_filters') {
          list = list.filter(u => !u.has_filters);
        }
        if (summarySearchQuery.value.trim()) {
          const q = summarySearchQuery.value.toLowerCase();
          list = list.filter(u => 
            (u.label && u.label.toLowerCase().includes(q)) || 
            (u.name && u.name.toLowerCase().includes(q)) || 
            (u.email && u.email.toLowerCase().includes(q)) || 
            (u.sublabel && u.sublabel.toLowerCase().includes(q)) || 
            String(u.id).includes(q)
          );
        }
        return list;
      });

      const availableTargetUsers = computed(() => {
        if (!selectedUser.value) return summaryData.value;
        return summaryData.value.filter(u => u.id !== selectedUser.value.id);
      });

      const openCloneModal = async () => {
        if (summaryData.value.length === 0) await loadSummary();
        cloneTargetUserIds.value = [];
        cloneMode.value = 'replace';
        isCloneModalOpen.value = true;
      };

      const executeClone = async () => {
        if (!selectedUser.value || cloneTargetUserIds.value.length === 0) return;
        isCloning.value = true;
        try {
          await apiFetch('/user-filters/copy', {
            method: 'POST',
            body: JSON.stringify({
              source_user_id: selectedUser.value.id,
              target_user_ids: cloneTargetUserIds.value,
              mode: cloneMode.value
            })
          });
          showToast(`Competenze clonate con successo su ${cloneTargetUserIds.value.length} operatore/i!`);
          isCloneModalOpen.value = false;
          await loadSummary();
        } catch (e) {
          showToast(e.message, 'error');
        } finally {
          isCloning.value = false;
        }
      };

      const goToConfigureUser = async (u) => {
        await selectUser(u);
        currentView.value = 'user';
      };

      const goToCloneUser = async (u) => {
        await selectUser(u);
        currentView.value = 'user';
        openCloneModal();
      };

      onMounted(() => {
        loadAvailableModels();
        loadDefinitions();
        loadSummary();
      });

      return {
        currentView,
        focusedField,
        toastMessage,
        toastType,
        isSubmitting,
        isCloning,
        availableModels,
        definitions,
        form,
        simulatedSql,
        formatClassName,
        setPivotMode,
        autoFillFields,
        loadDefinitions,
        saveDefinition,
        deleteDefinition,
        userSearchQuery,
        userSearchResults,
        isUserDropdownOpen,
        selectedUser,
        currentUserFilters,
        userForm,
        onUserSearchInput,
        onScopeFilterChange,
        selectUser,
        saveUserFilter,
        deleteUserFilter,
        isCloneModalOpen,
        cloneTargetUserIds,
        cloneMode,
        availableTargetUsers,
        openCloneModal,
        executeClone,
        summaryData,
        summaryStatusFilter,
        summarySearchQuery,
        summaryStats,
        filteredSummaryList,
        loadSummary,
        goToConfigureUser,
        goToCloneUser,
        rawAdditionalWhere,
        jsonError,
        validateJson,
        setJsonPreset
      };
    }
  }).mount('#app');
</script>
