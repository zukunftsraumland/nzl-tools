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
        return api.businessSectors.getAll().then((response) => {
            commit('setAll', response.data);
        });
    },

    load ({ commit }, id) {
        return api.businessSectors.get(id).then((response) => {
            commit('set', response.data);
        });
    },

};

// mutations
const mutations = {

    setAll (state, businessSectors) {
        state.all = businessSectors;
    },

    set (state, businessSector) {
        state.businessSector = businessSector;
    },

};

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
};