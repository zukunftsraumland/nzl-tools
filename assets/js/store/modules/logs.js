import api from '../../api';

// initial state
const state = () => ({
    all: [],
    filtered: [],
    log: {},
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
        commit('loaders/showLoader', 'logs', { root: true });
        return api.logs.getAll().then((response) => {
            commit('loaders/hideLoader', 'logs', { root: true });
            commit('setAll', response.data);
        });
    },

    loadFiltered ({ commit }, params) {
        commit('loaders/showLoader', 'logs', { root: true });
        return api.logs.getFiltered(params).then((response) => {
            commit('loaders/hideLoader', 'logs', { root: true });
            commit('setFiltered', response.data);
        });
    },

    load ({ commit }, id) {
        commit('loaders/showLoader', 'logs/'+id, { root: true });
        return api.logs.get(id).then((response) => {
            commit('loaders/hideLoader', 'logs/'+id, { root: true });
            commit('set', response.data);
        });
    },

    create ({ commit }, payload) {
        commit('loaders/showLoader', 'logs/create', { root: true });
        return api.logs.create(payload).then((response) => {
            commit('loaders/hideLoader', 'logs/create', { root: true });
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
        });
    },

    update ({ commit }, payload) {
        commit('loaders/showLoader', 'logs/'+payload.id, { root: true });
        return api.logs.update(payload.id, payload).then((response) => {
            commit('loaders/hideLoader', 'logs/'+payload.id, { root: true });
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
        commit('loaders/showLoader', 'logs/'+id, { root: true });
        return api.logs.delete(id).then((response) => {
            commit('loaders/hideLoader', 'logs/'+id, { root: true });
            commit('remove', id);
        });
    },

};

// mutations
const mutations = {

    setAll (state, logs) {
        state.all = logs;
    },

    setFiltered (state, filtered) {
        state.filtered = filtered;
    },

    set (state, log) {
        if(log) {
            log = {
                ...log,
                translations: typeof log.translations === 'object' && log.translations !== null && !Array.isArray(log.translations) ? log.translations : {},
            };
        }
        state.log = log;
    },

    insert (state, log) {
        state.all = [...state.all, log];
    },

    update (state, log) {
        let existingLog = state.all.find(p => p.id === log.id);
        if(existingLog) {
            state.all[state.all.indexOf(existingLog)] = log;
        }
    },

    remove (state, id) {
        state.all = state.all.filter((log) => {
            return parseInt(log.id) !== parseInt(id);
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