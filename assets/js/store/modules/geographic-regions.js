import api from '../../api';

// initial state
const state = () => ({
    all: [],
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
        return api.geographicRegions.getAll().then((response) => {
            commit('setAll', response.data);
        });
    },

    load ({ commit }, id) {
        return api.geographicRegions.get(id).then((response) => {
            commit('set', response.data);
        });
    },

};

// mutations
const mutations = {

    setAll (state, geographicRegions) {
        state.all = geographicRegions;
    },

    set (state, geographicRegion) {
        state.geographicRegion = geographicRegion;
    },

};

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
};