import axios from 'axios';

/**
 * Servizio centralizzato per le operazioni API del package FilterByModel.
 *
 * Gestisce tutte le chiamate REST verso le definizioni di filtro,
 * i modelli disponibili e i filtri assegnati agli utenti.
 */
export const filterService = {

    // ──────────────────────────────────────────
    // Definizioni di Filtro (Admin)
    // ──────────────────────────────────────────

    /**
     * Recupera tutte le definizioni di filtro configurate.
     */
    async getFilterDefinitions() {
        const response = await axios.get('/api/filter-definitions');
        return response.data;
    },

    /**
     * Salva (crea o aggiorna) una definizione di filtro.
     */
    async saveFilterDefinition(payload) {
        const response = await axios.post('/api/filter-definitions', payload);
        return response.data.data ? response.data.data : response.data;
    },

    /**
     * Elimina una definizione di filtro.
     */
    async deleteFilterDefinition(id) {
        await axios.delete(`/api/filter-definitions/${id}`);
    },

    // ──────────────────────────────────────────
    // Modelli Disponibili
    // ──────────────────────────────────────────

    /**
     * Recupera l'elenco dei modelli disponibili per la configurazione.
     */
    async getAvailableModels() {
        const response = await axios.get('/api/available-models');
        return response.data;
    },

    // ──────────────────────────────────────────
    // Filtri Utente
    // ──────────────────────────────────────────

    /**
     * Ricerca utenti per l'autocomplete (con supporto a tabella e campi custom).
     */
    async searchUsers(params = {}) {
        const response = await axios.get('/api/search-users', { params });
        return response.data;
    },

    /**
     * Recupera il resoconto/datatable di tutti gli utenti e lo stato dei loro filtri bindati.
     */
    async getUserFiltersSummary(params = {}) {
        const response = await axios.get('/api/user-filters-summary', { params });
        return response.data;
    },

    /**
     * Recupera i filtri attivi per un utente specifico.
     */
    async getUserFilters(userId) {
        const response = await axios.get('/api/user-filters', {
            params: { user_id: userId }
        });
        return response.data;
    },

    /**
     * Assegna un nuovo filtro a un utente.
     */
    async createUserFilter(payload) {
        const response = await axios.post('/api/user-filters', payload);
        return response.data.data ? response.data.data : response.data;
    },

    /**
     * Clona i filtri di un utente su uno o più utenti di destinazione.
     */
    async copyUserFilters(payload) {
        const response = await axios.post('/api/user-filters/copy', payload);
        return response.data;
    },

    /**
     * Rimuove un filtro utente.
     */
    async deleteUserFilter(id) {
        await axios.delete(`/api/user-filters/${id}`);
    },
};
