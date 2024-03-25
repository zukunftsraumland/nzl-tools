import api from '../../api';

// initial state
const state = () => ({
    all: [],
    filtered: [],
    contactGroup: {},
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
        commit('loaders/showLoader', 'contactGroups', { root: true });
        return api.contactGroups.getAll().then((response) => {
            commit('loaders/hideLoader', 'contactGroups', { root: true });
            commit('setAll', response.data);
            return response.data;
        });
    },

    loadFiltered ({ commit }, params) {
        commit('loaders/showLoader', 'contactGroups', { root: true });
        return api.contactGroups.getFiltered(params).then((response) => {
            commit('loaders/hideLoader', 'contactGroups', { root: true });
            commit('setFiltered', response.data);
            return response.data;
        });
    },

    load ({ commit }, id) {
        commit('loaders/showLoader', 'contactGroups/'+id, { root: true });
        return api.contactGroups.get(id).then((response) => {
            commit('loaders/hideLoader', 'contactGroups/'+id, { root: true });
            commit('set', response.data);
            return response.data;
        });
    },

    create ({ commit }, payload) {
        commit('loaders/showLoader', 'contactGroups/create', { root: true });
        return api.contactGroups.create(payload).then((response) => {
            commit('loaders/hideLoader', 'contactGroups/create', { root: true });
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

            return response;
        });
    },

    update ({ commit }, payload) {
        commit('loaders/showLoader', 'contactGroups/'+payload.id, { root: true });
        return api.contactGroups.update(payload.id, payload).then((response) => {
            commit('loaders/hideLoader', 'contactGroups/'+payload.id, { root: true });
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
        });
    },

    delete ({ commit }, id) {
        commit('loaders/showLoader', 'contactGroups/'+id, { root: true });
        return api.contactGroups.delete(id).then((response) => {
            commit('loaders/hideLoader', 'contactGroups/'+id, { root: true });
            commit('remove', id);
        });
    },

};

// mutations
const mutations = {

    setAll (state, contactGroups) {
        state.all = contactGroups;
    },

    setFiltered (state, filtered) {
        state.filtered = filtered;
    },

    set (state, contactGroup) {
        if(contactGroup) {
            contactGroup = {
                ...contactGroup,
                translations: typeof contactGroup.translations === 'object' && contactGroup.translations !== null && !Array.isArray(contactGroup.translations) ? contactGroup.translations : {},
            };
        }
        state.contactGroup = contactGroup;
    },

    insert (state, contactGroup) {
        state.all = [...state.all, contactGroup];
    },

    update (state, contactGroup) {
        let existingContactGroup = state.all.find(p => p.id === contactGroup.id);
        if(existingContactGroup) {
            state.all[state.all.indexOf(existingContactGroup)] = contactGroup;
        }
        existingContactGroup = state.filtered.find(p => p.id === contactGroup.id);
        if(existingContactGroup) {
            state.filtered[state.filtered.indexOf(existingContactGroup)] = contactGroup;
        }
    },

    remove (state, id) {
        state.all = state.all.filter((contactGroup) => {
            return parseInt(contactGroup.id) !== parseInt(id);
        });
        state.filtered = state.filtered.filter((contactGroup) => {
            return parseInt(contactGroup.id) !== parseInt(id);
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