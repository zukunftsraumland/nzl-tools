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
        return api.states.getAll().then((response) => {
            commit('setAll', response.data);
        });
    },

};

// mutations
const mutations = {

    setAll (state, states) {
        state.all = states;
    },

};

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
};