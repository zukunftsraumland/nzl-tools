import api from '../../api';

// initial state
const state = () => ({
    all: [],
    filtered: [],
    contact: {},
});

// getters
const getters = {

    getById: (state) => (id) => {
        return state.all.find(item => item.id === id);
    },

};

// actions
const actions = {

    loadAll ({ commit }) {
        commit('loaders/showLoader', 'contacts', { root: true });
        return api.contacts.getAll().then((response) => {
            commit('loaders/hideLoader', 'contacts', { root: true });
            commit('setAll', response.data);
            return response.data;
        });
    },

    loadFiltered ({ commit }, params) {
        commit('loaders/showLoader', 'contacts', { root: true });
        return api.contacts.getFiltered(params).then((response) => {
            commit('loaders/hideLoader', 'contacts', { root: true });
            commit('setFiltered', response.data);
            return response.data;
        });
    },

    load ({ commit }, id) {
        commit('loaders/showLoader', 'contacts/'+id, { root: true });
        return api.contacts.get(id).then((response) => {
            commit('loaders/hideLoader', 'contacts/'+id, { root: true });
            commit('set', response.data);
            return response.data;
        });
    },

    create ({ commit }, payload) {
        commit('loaders/showLoader', 'contacts/create', { root: true });
        return api.contacts.create(payload).then((response) => {
            commit('loaders/hideLoader', 'contacts/create', { root: true });
            if(payload.addToInbox) {
                if(payload.inboxId) {
                    commit('inbox/update', response.data, { root: true });
                } else {
                    commit('inbox/insert', response.data, { root: true });
                }
            } else {
                commit('insert', response.data);
                commit('set', response.data);
            }

            return response.data;
        });
    },

    update ({ commit }, payload) {
        commit('loaders/showLoader', 'contacts/'+payload.id, { root: true });
        return api.contacts.update(payload.id, payload).then((response) => {
            commit('loaders/hideLoader', 'contacts/'+payload.id, { root: true });
            if(payload.addToInbox) {
                if(payload.inboxId) {
                    commit('inbox/update', response.data, { root: true });
                } else {
                    commit('inbox/insert', response.data, { root: true });
                }
            } else {
                commit('update', response.data);
                commit('set', response.data);
            }

            return response.data;
        });
    },

    delete ({ commit }, id) {
        commit('loaders/showLoader', 'contacts/'+id, { root: true });
        return api.contacts.delete(id).then((response) => {
            commit('loaders/hideLoader', 'contacts/'+id, { root: true });
            commit('remove', id);

            return response.data;
        });
    },

};

// mutations
const mutations = {

    setAll (state, contacts) {
        state.all = contacts;
    },

    setFiltered (state, filtered) {
        state.filtered = filtered;
    },

    set (state, contact) {
        if(contact) {
            contact = {
                ...contact,
                translations: typeof contact.translations === 'object' && contact.translations !== null && !Array.isArray(contact.translations) ? contact.translations : {},
            };
        }
        state.contact = contact;
    },

    insert (state, contact) {
        state.all = [...state.all, contact];
    },

    update (state, contact) {
        let existingContact = state.all.find(p => p.id === contact.id);
        if(existingContact) {
            state.all[state.all.indexOf(existingContact)] = contact;
        }
        existingContact = state.filtered.find(p => p.id === contact.id);
        if(existingContact) {
            state.filtered[state.filtered.indexOf(existingContact)] = contact;
        }
    },

    remove (state, id) {
        state.all = state.all.filter((contact) => {
            return parseInt(contact.id) !== parseInt(id);
        });
        state.filtered = state.filtered.filter((contact) => {
            return parseInt(contact.id) !== parseInt(id);
        });
    },

};

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
};