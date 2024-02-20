import api from '../../api';

// initial state
const state = () => ({
    all: [],
    stint: null,
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
        return api.stints.getAll().then((response) => {
            commit('setAll', response.data);
        });
    },

    load ({ commit }, id) {
        return api.stints.get(id).then((response) => {
            commit('set', response.data);
        });
    },

};

// mutations
const mutations = {

    setAll (state, stints) {
        state.all = stints;
    },

    set (state, stint) {
        state.stint = stint;
    },

};

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
};