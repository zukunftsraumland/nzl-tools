import api from '../../api';

// initial state
const state = () => ({
    all: [],
    filtered: [],
    event: {},
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
        commit('loaders/showLoader', 'events', { root: true });
        return api.events.getAll().then((response) => {
            commit('loaders/hideLoader', 'events', { root: true });
            commit('setAll', response.data);
            return response.data;
        });
    },

    loadFiltered ({ commit }, params) {
        commit('loaders/showLoader', 'events', { root: true });
        return api.events.getFiltered(params).then((response) => {
            commit('loaders/hideLoader', 'events', { root: true });
            commit('setFiltered', response.data);
            return response.data;
        });
    },

    load ({ commit }, id) {
        commit('loaders/showLoader', 'events/'+id, { root: true });
        return api.events.get(id).then((response) => {
            commit('loaders/hideLoader', 'events/'+id, { root: true });
            commit('set', response.data);
            return response.data;
        });
    },

    create ({ commit }, payload) {
        commit('loaders/showLoader', 'events/create', { root: true });
        return api.events.create(payload).then((response) => {
            commit('loaders/hideLoader', 'events/create', { root: true });
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
        commit('loaders/showLoader', 'events/'+payload.id, { root: true });
        return api.events.update(payload.id, payload).then((response) => {
            commit('loaders/hideLoader', 'events/'+payload.id, { root: true });
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
        commit('loaders/showLoader', 'events/'+id, { root: true });
        return api.events.delete(id).then((response) => {
            commit('loaders/hideLoader', 'events/'+id, { root: true });
            commit('remove', id);
        });
    },

};

// mutations
const mutations = {

    setAll (state, events) {
        state.all = events;
    },

    setFiltered (state, filtered) {
        state.filtered = filtered;
    },

    set (state, event) {
        if(event) {
            event = {
                ...event,
                translations: typeof event.translations === 'object' && event.translations !== null && !Array.isArray(event.translations) ? event.translations : {},
            };
        }
        state.event = event;
    },

    insert (state, event) {
        state.all = [...state.all, event];
    },

    update (state, event) {
        let existingEvent = state.all.find(p => p.id === event.id);
        if(existingEvent) {
            state.all[state.all.indexOf(existingEvent)] = event;
        }
        existingEvent = state.filtered.find(p => p.id === event.id);
        if(existingEvent) {
            state.filtered[state.filtered.indexOf(existingEvent)] = event;
        }
    },

    remove (state, id) {
        state.all = state.all.filter((event) => {
            return parseInt(event.id) !== parseInt(id);
        });
        state.filtered = state.filtered.filter((event) => {
            return parseInt(event.id) !== parseInt(id);
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