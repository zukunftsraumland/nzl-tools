import api from '../../api';

// initial state
const state = () => ({
    all: [],
    filtered: [],
    region: {},
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
        commit('loaders/showLoader', 'regions', { root: true });
        return api.regions.getAll().then((response) => {
            commit('loaders/hideLoader', 'regions', { root: true });
            commit('setAll', response.data);
            return response.data;
        });
    },

    loadFiltered ({ commit }, params) {
        commit('loaders/showLoader', 'regions', { root: true });
        return api.regions.getFiltered(params).then((response) => {
            commit('loaders/hideLoader', 'regions', { root: true });
            commit('setFiltered', response.data);
            return response.data;
        });
    },

    load ({ commit }, id) {
        commit('loaders/showLoader', 'regions/'+id, { root: true });
        return api.regions.get(id).then((response) => {
            commit('loaders/hideLoader', 'regions/'+id, { root: true });
            commit('set', response.data);
            return response.data;
        });
    },

    create ({ commit }, payload) {
        commit('loaders/showLoader', 'regions/create', { root: true });
        return api.regions.create(payload).then((response) => {
            commit('loaders/hideLoader', 'regions/create', { root: true });
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
        commit('loaders/showLoader', 'regions/'+payload.id, { root: true });
        return api.regions.update(payload.id, payload).then((response) => {
            commit('loaders/hideLoader', 'regions/'+payload.id, { root: true });
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
        commit('loaders/showLoader', 'regions/'+id, { root: true });
        return api.regions.delete(id).then((response) => {
            commit('loaders/hideLoader', 'regions/'+id, { root: true });
            commit('remove', id);
        });
    },

};

// mutations
const mutations = {

    setAll (state, regions) {
        state.all = regions;
    },

    setFiltered (state, filtered) {
        state.filtered = filtered;
    },

    set (state, region) {
        if(region) {
            region = {
                ...region,
                translations: typeof region.translations === 'object' && region.translations !== null && !Array.isArray(region.translations) ? region.translations : {},
            };
        }
        state.region = region;
    },

    insert (state, region) {
        state.all = [...state.all, region];
    },

    update (state, region) {
        let existingRegion = state.all.find(p => p.id === region.id);
        if(existingRegion) {
            state.all[state.all.indexOf(existingRegion)] = region;
        }
        existingRegion = state.filtered.find(p => p.id === region.id);
        if(existingRegion) {
            state.filtered[state.filtered.indexOf(existingRegion)] = region;
        }
    },

    remove (state, id) {
        state.all = state.all.filter((region) => {
            return parseInt(region.id) !== parseInt(id);
        });
        state.filtered = state.filtered.filter((region) => {
            return parseInt(region.id) !== parseInt(id);
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